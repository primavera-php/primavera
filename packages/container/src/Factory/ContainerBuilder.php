<?php

namespace Primavera\Container\Factory;

use Composer\Autoload\ClassLoader;
use Primavera\Container\Bean\BeanRegisterer;
use Primavera\Container\ConfigurationData;
use Primavera\Container\Container\Container;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Container\Metadata\ParamMetadata;
use Primavera\Container\Scanner\ComponentScanner;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;
use Primavera\Event\EventDispatcher;
use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Metadata\MethodMetadata;

class ContainerBuilder
{
    /**
     * @var string[]
     */
    private array $namespaces = [];

    /**
     * @var string[]
     */
    private array $stereotypes = [];

    private ClassLoader $loader;

    private $beans = [];

    private $components = [];

    private $factories = [];

    private EventDispatcherInterface $eventDispatcher;

    private ?string $withYamlMetadata = null;

    private ?CacheInterface $cache = null;

    private bool $debug;

    private ?string $configFile = null;

    private ?string $withComponentScanner = ComponentScanner::class;

    public function __construct($debug = false)
    {
        $this->loader = require 'vendor/autoload.php';
        $this->eventDispatcher = new EventDispatcher();
        $this->debug = $debug;
    }

    public function withComponentScanner(?string $withComponentScanner): ContainerBuilder {
        $this->withComponentScanner = $withComponentScanner;

        return $this;
    }

    public function disableComponentScanner(): ContainerBuilder {
        $this->withComponentScanner = null;

        return $this;
    }

    public function withAllNamespaces()
    {
        $this->namespaces = [
            ...array_keys($this->loader->getPrefixes()),
            ...array_keys($this->loader->getPrefixesPsr4())
        ];

        return $this;
    }

    public function withAppNamespaces() {
        $composer = json_decode(file_get_contents('composer.json'), true);

        if (isset($composer['autoload']['psr-4'])) {
            $this->namespaces = array_merge($this->namespaces, array_keys($composer['autoload']['psr-4']));
        }

        if (isset($composer['autoload']['psr-0'])) {
            $this->namespaces = array_merge($this->namespaces, array_keys($composer['autoload']['psr-0']));
        }

        return $this;
    }

    public function withNamespaces(string ...$namespaces) {
        $this->namespaces = [...$this->namespaces, ...$namespaces];

        return $this;
    }

    public function withStereotypes(string ...$stereotypes) {
        $this->stereotypes = [...$this->stereotypes, ...$stereotypes];

        return $this;
    }

    public function withYamlMetadata(string $metadataPath) {
        $this->withYamlMetadata = $metadataPath;

        return $this;
    }

    public function withBeans(array $beans) {
        $this->beans = array_merge($this->beans, $beans);

        return $this;
    }

    public function withEventDispatcher(EventDispatcherInterface $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function withCache(CacheInterface $cache) {
        $this->cache = $cache;

        return $this;
    }

    public function withComponents(string ...$components) {
        $this->components = array_merge($this->components, $components);

        return $this;
    }

    public function withFactories(array $factories) {
        $this->factories = array_merge($this->factories, $factories);

        return $this;
    }

    public function withConfigFile(string $configFile) {
        $this->configFile = $configFile;

        return $this;
    }

    public function build(): Container {
        $factory = $this->buildMetadataFactory();

        $hasCache = !$this->debug && $this->cache && $this->cache->has('container');

        $container = $hasCache ? $this->cache->get('container') : new Container($factory, $this->eventDispatcher, debug: $this->debug);
        $container->set('debug', $this->debug);

        if ($this->configFile && !$hasCache) {
            $configData = new ConfigurationData($this->configFile);

            foreach ($configData as $id => $config) {
                $container->set($id, $config);
            }

            $container->set('config', $configData);
        }

        foreach ($this->beans as $id => $value) {
            $container->set($id, $value);
        }

        $componentScanner = null;

        if ($this->withComponentScanner) {
            $componentScanner = new ComponentScanner($factory, $this->debug, $this->cache);
            $container->set(get_class($componentScanner), $componentScanner);
        }
        
        $registerer = new BeanRegisterer(
            $container,
            $factory,
            $componentScanner,
            $this->namespaces,
            $this->stereotypes,
            $this->components,
            $this->factories,
        );

        if ($hasCache) {
            $container->set(get_class($factory), $factory);
            $container->set(get_class($this->eventDispatcher), $this->eventDispatcher);
            $registerer->postProccess();
        } else {
            $registerer->registerBeans();
        }

        if ($this->cache) {
            $this->cache->set('container', $container);
            $container->set(CacheInterface::class, $this->cache);
        }

        return $container;
    }

    private function buildMetadataFactory() {
        $factory = new MetadataFactoryFactory();

        if ($this->withYamlMetadata) {
            return $factory->createYmlMetadataFactory($this->withYamlMetadata, cache: $this->cache);
        }

        MethodMetadata::setParamMetadataClass(ParamMetadata::class);

        return $factory->createAnnotationMetadataFactory(ClassMetadata::class, cache: $this->cache);
    }
}