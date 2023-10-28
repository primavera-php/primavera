<?php

namespace PhpBeans\Container;

use PhpBeans\Annotation\Configuration;
use PhpBeans\Annotation\Imports;
use PhpBeans\Event\AfterInstanceBeanEvent;
use PhpBeans\Event\BeforeInstanceBeanEvent;
use PhpBeans\Metadata\ClassMetadata;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Vox\Event\EventDispatcher;
use Vox\Log\Logger;
use Vox\Metadata\ClassMetadataInterface;
use Vox\Metadata\Factory\MetadataFactoryInterface;
use Vox\Metadata\MethodMetadata;
use Vox\Metadata\MethodMetadataInterface;
use Vox\Metadata\ParamMetadata;

class Container implements ContainerInterface, \IteratorAggregate
{
    /** 
     * @var IdentifiedDataBag<ClassMetadata>
     */
    private IdentifiedDataBag $metadatas;

    /**
     *  @var IdentifiedDataBag<mixed> 
     */
    private IdentifiedDataBag $beans;

    /**
     *  @var IdentifiedDataBag<MethodMetadata>
     */
    private IdentifiedDataBag $methodMetadatas;

    private array $interfaces = [];

    private array $statuses = [];

    private LoggerInterface $logger;

    private const INSTANTIATING = 1;
    private const INSTANTIATED = 2;

    public function __construct(
        /** @var MetadataFactoryInterface<\PhpBeans\Metadata\ClassMetadata> */
        private MetadataFactoryInterface $metadataFactory,
        private EventDispatcherInterface $eventDispatcher = new EventDispatcher(),
        private bool $debug = false,
    ) {
        $this->initialize();
    }

    private function initialize()
    {
        $this->logger = Logger::getLogger(__CLASS__);

        $this->metadatas ??= new IdentifiedDataBag($this->metadataFactory);
        $this->beans ??= new IdentifiedDataBag($this->metadataFactory);
        $this->methodMetadatas ??= new IdentifiedDataBag($this->metadataFactory);
        
        $this->set('container', $this);
        $this->set(MetadataFactoryInterface::class, $this->metadataFactory);
        $this->set(EventDispatcherInterface::class, $this->eventDispatcher);
    }

    public function set(mixed $id, mixed $value)
    {
        if ($value instanceof ClassMetadataInterface && $value->getReflection()->isInterface()) {
            $this->interfaces[$id] = $value;
        } elseif ($value instanceof ClassMetadataInterface) {
            $this->metadatas[$id] = $value;

            $annotations = $value->getAnnotations();

            foreach ($annotations as $annotation) {
                $annotMetadata = $this->metadataFactory->getMetadataForClass($annotation::class);

                if ($annotMetadata->hasAnnotation(Imports::class) || $annotMetadata->getName() === Imports::class) {
                    $imports = $annotMetadata->getName() === Imports::class 
                        ? $annotation 
                        : $annotMetadata->getAnnotation(Imports::class);

                    foreach ($imports->configurations as $config) {
                        $configMetadata = $this->metadataFactory->getMetadataForClass($config);
                        $configMetadata->annotations[Configuration::class] = new Configuration();
                        $this->set($configMetadata->name, $configMetadata);
                    }
                }
            }

        } elseif ($value instanceof MethodMetadataInterface) {
            $this->methodMetadatas[$id] = $value;

            $this->metadatas[$id] = $this->metadataFactory->getMetadataForClass($value->getType());
        } elseif (is_string($value) && interface_exists($value)) {
            $this->interfaces[$id] = $this->metadataFactory->getMetadataForClass($value);
        } else {
            $this->beans[$id] = $value;
            
            if (is_object($value) && !$value instanceof self)
                $this->metadatas[$id] = $this->metadataFactory->getMetadataForClass(get_class($value));
        }
    }

    public function get(string $id) 
    {
        if (isset($this->beans[$id])) {
            return $this->beans[$id];
        }

        $this->beans[$id] = $bean = $this->newInstance($id);

        if ($bean instanceof ContainerAwareInterface) {
            $bean->setContainer($this);
        }

        return $this->beans[$id];
    }

    public function has(string $id)
    {
        return isset($this->beans[$id])
            || isset($this->metadatas[$id])
            || isset($this->methodMetadatas[$id]);
    }

    private function newInstance(string $id)
    {
        if ($this->isIstantiating($id)) {
            throw new CircularReferenceException($id);
        }

        $this->statuses[$id] = self::INSTANTIATING;

        $this->eventDispatcher
            ->dispatch(new BeforeInstanceBeanEvent($this->metadatas[$id] ?? $this->methodMetadatas[$id], $id));

        if (isset($this->methodMetadatas[$id])) {
            $bean = $this->newIntanceFromMethodMetadata($id);
        } elseif (isset($this->metadatas[$id])) {
            $bean = $this->newIntanceFromMetadata($id);
        }

        $this->statuses[$id] = self::INSTANTIATED;

        $this->eventDispatcher->dispatch(new AfterInstanceBeanEvent($bean, $id));

        return $this->beans[$id] = $bean;
    }

    private function newIntanceFromMetadata(string $id)
    {
        $params = ($metadata = $this->metadatas[$id])->getConstructorParams();

        return $metadata->getReflection()
            ->newInstanceArgs(array_map(fn($p) => $this->get($p->getId()), $params));
    }

    private function newIntanceFromMethodMetadata(string $id)
    {
        $params = ($metadata = $this->methodMetadatas[$id])->getParams();

        return $metadata->invoke(
            $this->get($metadata->getClass()),
            ...array_map(fn(ParamMetadata $p) => $this->get($p->getId()), $params)
        );
    }

    private function isIstantiating($id)
    {
        return ($this->statuses[$id] ?? null) === self::INSTANTIATING;
    }

    private function isIstantiated($id)
    {
        return ($this->statuses[$id] ?? null) === self::INSTANTIATED;
    }

    /**
     * @return ClassMetadata[]
     */
    public function getMetadadasByStereotype(string $stereotype): array
    {
        return $this->metadatas->filter(
            fn(ClassMetadata $m) => $m->hasAnnotation($stereotype) || in_array($stereotype, $m->getHierarchy()
        ));
    }

    public function getComponentsByStereotype(string $stereotype): \Generator
    {
        foreach ($this->getMetadadasByStereotype($stereotype) as $id => $metadata) {
            yield $id => $this->get($id);
        }
    }

	/**
	 * @return ClassMetadata[]
	 */
	public function getInterfaces(): array {
		return $this->interfaces;
	}

    public function hasMetadata(string $id)
    {
        return isset($this->metadatas[$id]);
    }
    
    public function getMetadata(string $id)
    {
        return $this->metadatas[$id];
    }

    public function getEvemtDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    public function __serialize()
    {
        return [
            $this->metadatas,
            $this->methodMetadatas,
            $this->metadataFactory,
            $this->eventDispatcher,
        ];
    }

    public function __unserialize(array $data)
    {
        [
            $this->metadatas,
            $this->methodMetadatas,
            $this->metadataFactory,
            $this->eventDispatcher,
        ] = $data;

        $this->initialize();
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->metadatas->keys() as $id) {
            yield $id => $this->get($id);
        }
    }
}
