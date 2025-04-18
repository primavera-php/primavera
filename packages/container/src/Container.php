<?php

namespace Primavera\Container;

use IteratorAggregate;
use Primavera\Commons\Collection\Collection;
use Primavera\Commons\Collection\CollectionInterface;
use Primavera\Commons\Collection\TreeCollection;
use Primavera\Commons\Collection\UniqueCollection;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\Annotation\Injects;
use Primavera\Container\Event\AfterComponentRegisterEvent;
use Primavera\Container\Event\AfterInstanceComponentEvent;
use Primavera\Container\Event\BeforeComponentRegisterEvent;
use Primavera\Container\Event\BeforeGetComponent;
use Primavera\Container\Event\BeforeInstanceComponentEvent;
use Primavera\Container\Exception\ContainerException;
use Primavera\Container\Exception\NotFoundContainerException;
use Primavera\Event\EventDispatcher;
use Primavera\Event\EventDispatcherInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\MethodMetadataInterface;
use Primavera\Metadata\ParamMetadata;
use Primavera\Metadata\ParamMetadataInterface;

class Container implements DependencyResolverContainerInterface, WritableContainerInterface, TransientContainerInterface, ScopedContainerInterface, IteratorAggregate
{
    private AliasMapper $components;

    /**
     * @var class-string[]
     */
    private UniqueCollection $transient;

    /**
     * @var class-string[]
     */
    private UniqueCollection $scoped;

    private array $statuses = [];

    private TreeCollection $stack;

    private CollectionInterface $values;

    private const INSTANCING = 1;

    private const INSTANCED = 2;

    public function __construct(
        private MetadataFactoryInterface $metadataFactory,
        private EventDispatcherInterface $eventDispatcher = new EventDispatcher(),
    ) {
        $this->components = new AliasMapper($metadataFactory);
        $this->transient = new UniqueCollection();
        $this->scoped = new UniqueCollection();
        $this->values = new Collection();
        $this->stack = new TreeCollection();
        $this->set('metadata-factory', $metadataFactory);
        $this->set('event-dispatcher', $eventDispatcher);
    }

    /**
     * @template T
     * 
     * @param class-string<T> | string $id
     * 
     * @return T
     */
    public function get(string $id, ParamMetadataInterface $paramMetadata = null)
    {
        $this->eventDispatcher->dispatch($event = new BeforeGetComponent($this, $id, $paramMetadata));

        if ($event->result)
            return $event->result;

        if ($this->values->has($id))
            return $this->values->get($id);

        if (class_exists($id, true) && !$this->components->has($id))
            return $this->newInstance($id, $paramMetadata);

        $item = $this->components->get($id, $paramMetadata)[0] ?? throw new NotFoundContainerException($id, $paramMetadata);

        if (is_string($item))
            return $this->newInstance($item, $paramMetadata);

        return $item;
    }

    public function set(string $id, $value): self
    {
        if ($value instanceof MethodMetadataInterface) {
            $this->addFactory($id, $value->getType(), $value);

            return $this;
        }

        if ((is_object($value) || (is_string($value)) && class_exists($value, true))) {
            if (is_object($value))
                $this->eventDispatcher->dispatch(new BeforeComponentRegisterEvent($this, $value));
            
            $this->components->set($id, $value);

            if ($value instanceof ContainerAwareInterface)
                $value->setContainer($this);

            if (is_object($value))
                $this->eventDispatcher->dispatch(new AfterComponentRegisterEvent($this, $value));

            return $this;
        }

        if (is_string($value) && interface_exists($value, true)) {
            throw new ContainerException("cannot add interfaces to the container: {$value}");
        }

        $this->values->add($value, $id);

        return $this;
    }

    public function has(string $id): bool
    {
        return $this->values->has($id)
            || $this->components->has($id)
            || $this->transient->has($id);
    }

    private function addFactory(string $id, $className, MethodMetadataInterface $factory = null): array
    {
        $aliases = $this->components->set($id, $className);

        if ($factory) {
            $this->components->addFactory($id, $factory);
        }

        return $aliases;
    }

    public function addTransient(string $id, $className, MethodMetadataInterface $factory = null): self
    {
        $aliases = $this->addFactory($id, $className, $factory);

        foreach ($aliases as $alias) {
            $this->transient->add($alias);
        }

        return $this;
    }

    public function addScoped(string $id, $className, MethodMetadataInterface $factory = null): self
    {
        $aliases = $this->addFactory($id, $className, $factory);

        foreach ($aliases as $alias) {
            $this->scoped->add($alias);
        }

        return $this;
    }

    private function newInstance(string $classname, ParamMetadata $paramMetadata = null)
    {
        if (isset($this->statuses[$classname]) && $this->statuses[$classname] == self::INSTANCING) {
            throw new ContainerException("circular reference found for {$classname} on {$paramMetadata?->getClass()}");
        }

        $this->statuses[$classname] ??= [];
        $this->statuses[$classname] = self::INSTANCING;

        $metadata = $this->metadataFactory->getMetadataForClass($classname);
        $ctor = $metadata->getMethodMetadata()['__construct'] ?? null;
        $depends = (
            $this->components->hasFactory($metadata->getName()) 
                ? $this->components->getFactory($metadata->getName())->getParams() 
                : $ctor?->getParams()
            ) ?? [];

        foreach ($depends as &$depend) {
            $type = $depend->getType();
            $alias = $depend->getAnnotation(Injects::class)?->id;

            if (is_array($type) && !$alias) {
                throw new ContainerException("composed types isn't supported by the container, try use an alias");
            }

            $id = $alias ?? $type ?? $depend->getName();

            if ($id === $type && $depend->isNativeType()) {
                $id = $depend->getName();
            }

            try {
                $depend = $this->get($id, $depend);
            } catch (NotFoundContainerException $e) {
                if ($depend->getReflection()->isOptional()) {
                    $depend = $depend->getReflection()->getDefaultValue();
                } else {
                    throw $e;
                }
            }
        }

        $this->eventDispatcher->dispatch($beforeEvent = new BeforeInstanceComponentEvent($this, $classname, $depends, $paramMetadata));

        $factory = null;
        $instance = $beforeEvent->result 
            ?? ($this->components->hasFactory($metadata->getName())
                ? ($factory = $this->components->getFactory($metadata->getName()))
                    ->invoke($this->get($factory->getClass()), ...$depends)
                : $metadata->getReflection()->newInstanceArgs($depends));

        $this->eventDispatcher->dispatch(new AfterInstanceComponentEvent($this, $instance));

        if (!$this->transient->has($classname)) {
            $this->set($classname, $instance);

            if ($factory)
                $this->components->addAlias(
                    $factory->getAnnotation(Factory::class)?->name
                        ?? $factory->getName(),
                    $instance
                );
        }

        $this->statuses[$classname] = self::INSTANCED;

        return $instance;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->components as $id => $item) {
            if (!is_object($item)) {
                $item = $this->get($item);
            }

            yield $id => $item;
        }
    }
}
