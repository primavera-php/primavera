<?php

namespace Primavera\Container\Bean;

use Primavera\Container\Annotation\Configurator;
use Primavera\Container\ContainerException;
use Primavera\Container\Factory\StereotypeFactoryInterface;
use Primavera\Container\ParamResolverInterceptorInterface;
use Primavera\Metadata\Factory\MetadataFactory;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Component;
use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\PostBeanProcessor;
use Primavera\Container\Annotation\Value;
use Primavera\Container\Container;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Primavera\Container\Processor\PostBeanStereotypeProcessor;
use Primavera\Container\Scanner\ComponentScanner;
use Psr\Log\LoggerInterface;
use Primavera\Log\Logger;

class BeanRegisterer
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

    /**
     * @var string[]
     */
    private array $components = [];

    private Container $container;

    private LoggerInterface $logger;

    public function __construct(
        Container $container,
        private MetadataFactory $metadataFactory,
        private ?ComponentScanner $componentScanner = null,
        array $namespaces = [],
        array $stereotypes = [],
        array $components = [],
        private array $factories = [],
    ) {
        $this->componentScanner = $componentScanner;
        $this->container = $container;

        $this->namespaces = array_merge(['Primavera\Container\\'], $namespaces);
        $this->stereotypes = array_merge(
            [
                Component::class,
                Configuration::class,
                PostBeanProcessor::class,
                AbstractStereotypeProcessor::class,
                AbstractInterfaceImplementor::class,
                StereotypeFactoryInterface::class,
                ParamResolverInterceptorInterface::class,
            ],
            $stereotypes
        );
        $this->components = array_merge(
            [
                ValuePostBeanProcessor::class,
                PostBeanStereotypeProcessor::class,
                AutowirePostBeanProcessor::class,
            ],
            $components
        );

        $this->logger = Logger::getLogger(__CLASS__);
    }

    public function addNamespace(string $namespace)
    {
        $this->namespaces[] = $namespace;

        return $this;
    }

    public function addStereotype(string $class)
    {
        $this->stereotypes[] = $class;

        return $this;
    }

    public function addComponent(string $class)
    {
        $this->components[] = $class;

        return $this;
    }

    public function getAllComponentsMetadata()
    {
        return array_map(fn($class) => $this->metadataFactory->getMetadataForClass($class), $this->components);
    }

    public function configure()
    {
        foreach ($this->container->getMetadadasByStereotype(Configuration::class) as $config) {
            foreach ($config->getAnnotatedMethods(Configurator::class) as $configurator) {
                if (!$configurator->getReflection()->isStatic()) {
                    throw new ContainerException('a configurator method must be static');
                }

                $configurator->invoke(null, $this);
            }
        }
    }

    public function registerClassComponents(ClassMetadata $classMetadata)
    {
        foreach ($classMetadata->getAnnotations() as $stereotype) {
            $this->registerComponent($classMetadata, get_class($stereotype));
        }
    }

    public function registerComponents()
    {
        foreach ($this->getAllComponentsMetadata() as $metadata) {
            $this->registerComponent($metadata);
        }

        if ($this->componentScanner) {
            $this->scanComponents(Configuration::class);
            $this->scanComponents(StereotypeFactoryInterface::class);
            $this->scanComponents(ParamResolverInterceptorInterface::class);
        }

        if (!$this->componentScanner) {
            array_map([$this, 'registerClassComponents'], $this->getAllComponentsMetadata());

            return;
        }

        $this->configure();

        foreach ($this->stereotypes as $stereotypeClass) {
            if ($stereotypeClass == Configuration::class)
                continue;

            $this->scanComponents($stereotypeClass);
        }
    }

    private function scanComponents($stereotypeClass)
    {
        $namespaces = $this->namespaces;

        $components = array_merge(
            $this->componentScanner->scanComponentsFor($stereotypeClass, ...$namespaces),
            $this->getAllComponentsMetadata(),
        );

        foreach ($components as $metadata) {
            $this->registerComponent($metadata, $stereotypeClass);
        }
    }

    public function registerFactories()
    {
        foreach ($this->container->getMetadadasByStereotype(Configuration::class) as $config) {
            foreach ($config->getAnnotatedMethods(Bean::class) as $factory) {
                $type = $factory->type;
                $id = $factory->getAnnotation(Bean::class)->name ?? $type ?? $factory->name;
                $this->container->set($id, $factory);
            }
        }
    }

    public function registerComponent(ClassMetadata $metadata, string $stereotypeClass = null)
    {
        $this->container->set($metadata->name, $metadata);

        if ($stereotypeClass && $metadata->hasAnnotation($stereotypeClass)) {
            $component = $metadata->getAnnotation($stereotypeClass);

            if (property_exists($component, 'name') && $component->name) {
                $this->container->set($component->name, $metadata);
            }
        }

        return $this;
    }

    public function resolveConfigurationValues()
    {
        foreach ($this->container->getComponentsByStereotype(Configuration::class) as $configuration) {
            $metadata = $this->metadataFactory->getMetadataForClass(get_class($configuration));

            foreach($metadata->getAnnotatedProperties(Value::class) as $property) {
                $this->process($property, $configuration, $this->container, $property->getAnnotation(Value::class));
            }
        }
    }

    public function registerBeans() 
    {
        $this->registerComponents();
        $this->resolveConfigurationValues();
        $this->registerFactories();
        $this->implementInterfaces();
        $this->postProccess();
    }

    public function implementInterfaces()
    {
        $interfaceImplementors = $this->container
            ->getComponentsByStereotype(AbstractInterfaceImplementor::class);

        foreach ($this->container->getInterfaces() as $interface) {
            /* @var $interfaceImplementor AbstractInterfaceImplementor */                     
            foreach ($interfaceImplementors as $interfaceImplementor) {
                if (!$interfaceImplementor->accept($interface)) {
                    continue;
                }

                $interfaceImplementor->implementsClass($interface);
            }
        }
    }

    public function postProccess()
    {
        foreach ($this->container->getComponentsByStereotype(PostBeanProcessor::class) as $component) {
            $invokeMetadata = $this->container->getMetadata(get_class($component))->getMethodMetadata()['__invoke'] ?? throw new ContainerException('post p´rocessor must be a invokable class');
            $params = array_map(fn($p) => $this->container->get($p->getId()), $invokeMetadata->getParams());

            $component(...$params);
        }

        /* @var $processor AbstractStereotypeProcessor */
        foreach ($this->container->getComponentsByStereotype(AbstractStereotypeProcessor::class) as $processor) {
            $processor->findAndProcess();
        }
    }
}
