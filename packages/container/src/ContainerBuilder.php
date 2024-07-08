<?php

namespace Primavera\Container;

use Composer\Autoload\ClassLoader;
use Primavera\Container\Annotation\Component;
use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Annotation\EventListener;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\Annotation\Imports;
use Primavera\Container\Annotation\Value;
use Primavera\Container\ConfigurationData;
use Primavera\Container\Container;
use Primavera\Container\Processor\AutowirePostProcessor;
use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactory;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Primavera\Event\EventDispatcher;
use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Event\EventDispatcherInterface;

class ContainerBuilder implements ContainerBuilderInterface
{
    use ValueProcessorTrait;

    /**
     * @var string[]
     */
    private array $namespaces = [];

    /**
     * @var string[]
     */
    private array $stereotypes = [];

    private ClassLoader $loader;

    private $instances = [];

    private $components = [
        AutowirePostProcessor::class,
    ];

    private $factories = [];

    private EventDispatcherInterface $eventDispatcher;

    private ?string $withYamlMetadata = null;

    private ?CacheInterface $cache = null;

    private bool $debug;

    private ?string $configFile = null;

    private bool $shouldScanComponents = true;

    public function __construct($debug = false)
    {
        $this->loader = require 'vendor/autoload.php';
        $this->eventDispatcher = new EventDispatcher();
        $this->debug = $debug;
    }

    public function shouldScanComponents(bool $should): ContainerBuilder 
    {
        $this->shouldScanComponents = $should;

        return $this;
    }

    public function withAllNamespaces(): self
    {
        $this->namespaces = [
            ...array_keys($this->loader->getPrefixes()),
            ...array_keys($this->loader->getPrefixesPsr4())
        ];

        return $this;
    }

    public function withAppNamespaces(): self
    {
        $composer = json_decode(file_get_contents('composer.json'), true);

        if (isset($composer['autoload']['psr-4'])) {
            $this->namespaces = array_merge($this->namespaces, array_keys($composer['autoload']['psr-4']));
        }

        if (isset($composer['autoload']['psr-0'])) {
            $this->namespaces = array_merge($this->namespaces, array_keys($composer['autoload']['psr-0']));
        }

        return $this;
    }

    public function withNamespaces(string ...$namespaces): self
    {
        $this->namespaces = [...$this->namespaces, ...$namespaces];

        return $this;
    }

    public function withStereotypes(string ...$stereotypes): self
    {
        $this->stereotypes = [...$this->stereotypes, ...$stereotypes];

        return $this;
    }

    public function withYamlMetadata(string $metadataPath): self
    {
        $this->withYamlMetadata = $metadataPath;

        return $this;
    }

    public function withInstances(array $instances): self
    {
        $this->instances = array_merge($this->instances, $instances);

        return $this;
    }

    public function withEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function withCache(CacheInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public function withComponents(string ...$components): self
    {
        $this->components = array_merge($this->components, $components);

        return $this;
    }

    public function withFactories(array $factories): self
    {
        $this->factories = array_merge($this->factories, $factories);

        return $this;
    }

    public function withConfigFile(string $configFile): self
    {
        $this->configFile = $configFile;

        return $this;
    }

    public function build(): ContainerInterface
    {
        $mf = $this->buildMetadataFactory();
        $container = new Container($mf, $this->eventDispatcher, $this->debug);
        $postProcessors = [];
        $eventListeners = [];

        if ($this->configFile) {
            $configData = new ConfigurationData($this->configFile);

            foreach ($configData as $id => $config)
                $container->set($id, $config);

            $container->set('config', $configData);
        }

        foreach ($this->instances as $id => $instance)
            $container->set($id, $instance);

        $addMetadata = function (ClassMetadataInterface $metadata) use (&$postProcessors, &$eventListeners) {
            if ($metadata->hasAnnotation(EventListener::class))
                $eventListeners[] = $metadata;

            if ($metadata->instanceof(ComponentPostProcessorInterface::class))
                $postProcessors[] = $metadata;
        };

        foreach ($mf as $metadata) {
            if ($metadata->hasAnnotation(Component::class))
                $this->withComponents($metadata->getName());

            if ($metadata->hasAnnotation(Configuration::class))
                $this->handleConfigurator($metadata, $container);

            $addMetadata($metadata);
        }

        foreach ($this->components as $component) {
            $metadata = $mf->getMetadataForClass($component);
            $addMetadata($metadata);

            $container->set($component, $component);
        }

        foreach ($eventListeners as $eventListener) {
            $eventListener = $container->get($eventListener->getName());
            $this->eventDispatcher->registerListener($eventListener);
        }

        foreach ($container as $id => $item) {
            foreach ($postProcessors as $postProcessor) {
                $postProcessor = $container->get($postProcessor->getName());

                if ($postProcessor->canProcess($item))
                    $postProcessor->process($item, $container);
            }
        }

        return $container;
    }

    private function buildMetadataFactory()
    {
        if ($this->cache && $this->cache->has('metadata-factory'))
            return $this->cache->get('metadata-factory');

        $factory = new MetadataFactoryFactory();

        $mf = $this->withYamlMetadata 
            ? $factory->createYmlMetadataFactory($this->withYamlMetadata, cache: $this->cache)
            : $factory->createAnnotationMetadataFactory(cache: $this->cache);

        $this->scanComponents($mf);

        if ($this->cache)
            $this->cache->set('metadata-factory', $mf);

        return $mf;
    }

    private function scanComponents(MetadataFactory $factory)
    {
        if ($this->shouldScanComponents) {
            foreach ($this->namespaces as $namespace) {
                $paths = array_unique(
                    array_merge(
                        $this->loader->getPrefixes()[$namespace] ?? [],
                        $this->loader->getPrefixesPsr4()[$namespace] ?? []
                    )
                );

                foreach ($paths as $path)
                    $factory->loadFromFolder($path);
            }
        }
    }

    private function handleConfigurator(ClassMetadataInterface $config, Container $container)
    {
        foreach ($config->getAnnotatedMethodsMetadata(Configurator::class) as $configurator) {
            if (!$configurator->getReflection()->isStatic())
                throw new ContainerException('a configurator method must be static');

            $configurator->invoke(null, $this);
        }

        foreach (array_filter($config->getPropertyMetadata(), fn($p) => $p->isStatic()) as $staticProp) {
            if (!$staticProp->hasAnnotation(Value::class))
                continue;

            $this->process($staticProp, $config, $container, Value::class);
        }

        foreach ($config->getAnnotatedMethodsMetadata(Factory::class) as $factory)
            $container->set($factory->getName(), $factory);

        foreach ($this->handleImports($config, $container->get(MetadataFactoryInterface::class)) as $imported)
            $this->handleConfigurator($imported, $container);
    }

    private function handleImports(ClassMetadataInterface $component, MetadataFactoryInterface $mf)
    {
        $annotations = $component->getAnnotations();

        foreach ($annotations as $annotation) {
            $annotMetadata = $mf->getMetadataForClass($annotation::class);

            if ($annotMetadata->hasAnnotation(Imports::class) || $annotMetadata->getName() === Imports::class) {
                $imports = $annotMetadata->getName() === Imports::class 
                    ? $annotation 
                    : $annotMetadata->getAnnotation(Imports::class);

                foreach ($imports->configurations as $config) {
                    yield $mf->getMetadataForClass($config);
                }
            }
        }
    }
}