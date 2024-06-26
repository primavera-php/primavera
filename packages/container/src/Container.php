<?php

namespace Primavera\Container;

use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Imports;
use Primavera\Container\Annotation\Transient;
use Primavera\Container\Event\AfterInstanceBeanEvent;
use Primavera\Container\Event\BeforeInstanceBeanEvent;
use Primavera\Container\Factory\StereotypeFactoryInterface;
use Primavera\Container\Metadata\ClassMetadata;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Primavera\Event\EventDispatcher;
use Primavera\Log\Logger;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\MethodMetadataInterface;
use Primavera\Container\Metadata\ParamMetadata;

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

    /**
     * @var ClassMetadata[]
     */
    private array $stereotypeFactories = [];

    /**
     * @var ParamResolverInterceptorInterface[]
     */
    private array $paramResolverInterceptors = [];

    private LoggerInterface $logger;

    private const INSTANTIATING = 1;
    private const INSTANTIATED = 2;
    private const ERROR = 3;

    public function __construct(
        /** @var MetadataFactoryInterface<\Primavera\Container\Metadata\ClassMetadata> */
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

            if ($value->instanceOf(StereotypeFactoryInterface::class)) {
                if (!$value->hasGenerics()) {
                    throw new ContainerException(
                        "the Stereotype factory {$value->getName()} must have generics info using the @extends docblock, so the code can know what this builds"
                    );
                }

                $this->stereotypeFactories[$value->getGenericsInfo()['decoration']] = $value;
            }

            if ($value->instanceOf(ParamResolverInterceptorInterface::class)) {
                $this->paramResolverInterceptors[] = $this->get($value->getName());
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

    /**
     * @template T
     * 
     * @param class-string<T> $id
     * 
     * @return T
     */
    public function get(string $id) 
    {
        $isTransitent = (isset($this->methodMetadatas[$id]) && $this->methodMetadatas[$id]->hasAnnotation(Transient::class)) 
            || (isset($this->metadatas[$id]) && $this->metadatas[$id]->hasAnnotation(Transient::class));

        if (isset($this->beans[$id]) && !$isTransitent) {
            return $this->beans[$id];
        }

        if ($isTransitent) {
            return $this->newInstance($id);
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

    /**
     * @param ParamMetadata[] $params
     */
    protected function resolveParams(array $params)
    {
        return array_map(
            function (ParamMetadata $p) {
                try {
                    foreach ($this->paramResolverInterceptors as $paramInterceptor) {
                        if ($paramInterceptor->canIntercept($p)) {
                            return $paramInterceptor->resolve($p, $this);
                        }
                    }
                    
                    return $this->get($p->getId());
                } catch (NotFoundContainerException $e) {
                    $this->statuses[$p->getId()] = self::ERROR;
                    
                    if ($p->getReflection()->isOptional()) {
                        return $p->getReflection()->getDefaultValue();
                    }

                    throw $e;
                }
            },
            $params
        );
    }

    private function newIntanceFromMetadata(string $id)
    {
        $params = ($metadata = $this->metadatas[$id])->getConstructorParams();

        $stereotypes = [
            ...array_map(fn($a) => $a::class, $metadata->getAnnotations()),
            ...$metadata->getInterfaces(),
            ...$metadata->getHierarchy()
        ];

        foreach ($stereotypes as $stereotype) {
            if (isset($this->stereotypeFactories[$stereotype])) {
                return $this->get(($this->stereotypeFactories[$stereotype])->getName())
                    ->create($this, $metadata, $params);
            }
        }

        return $metadata->getReflection()
            ->newInstanceArgs($this->resolveParams($params));
    }

    private function newIntanceFromMethodMetadata(string $id)
    {
        $params = ($metadata = $this->methodMetadatas[$id])->getParams();

        return $metadata->invoke(
            $this->get($metadata->getClass()),
            ...$this->resolveParams($params)
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
            fn(ClassMetadata $m) => $m->hasAnnotation($stereotype) 
                || in_array($stereotype, $m->getHierarchy())
                || $m->isInstanceOf($stereotype)
        );
    }

    public function getComponentsByStereotype(string $stereotype): array
    {
        $data = [];

        foreach ($this->getMetadadasByStereotype($stereotype) as $id => $metadata) {
            if ($metadata->hasAnnotation(Transient::class) 
                || (isset($this->methodMetadatas[$id]) && $this->methodMetadatas[$id]->hasAnnotation(Transient::class))) {
                continue;
            }

            $data[$id] = $this->get($id);
        }

        return $data;
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
            $this->stereotypeFactories,
        ];
    }

    public function __unserialize(array $data)
    {
        [
            $this->metadatas,
            $this->methodMetadatas,
            $this->metadataFactory,
            $this->eventDispatcher,
            $this->stereotypeFactories,
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
