<?php

namespace Primavera\Container;

use IteratorAggregate;
use Primavera\Commons\Collection\UniqueCollection;
use Primavera\Container\Exception\ContainerException;
use Primavera\Container\Exception\NotFoundContainerException;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\MethodMetadataInterface;

class AliasMapper implements WritableContainerInterface, ComponentFactoryStorageInterface, IteratorAggregate
{
    private array $objects = [];

    private array $classes = [];

    private UniqueCollection $addedClasses;

    private array $aliases = [];

    private array $factories = [];

    public function __construct(
        private MetadataFactoryInterface $metadataFactory
    ) {
        $this->addedClasses = new UniqueCollection();
    }

    public function set(string $id, $value): array
    {
        $objectHash = null;
        $class = null;

        if (is_object($value)) {
            $objectHash = spl_object_hash($value);

            if (!isset($this->objects[$objectHash])) {
                $this->objects[$objectHash] = $value;
            }
        } elseif(class_exists($value, true)) {
            $class = $value;
            $this->addedClasses->add($class);
        } else {
            throw new ContainerException("Object instance or classs name is required to store");
        }

        $metadata = $this->metadataFactory->getMetadataForClass(is_object($value) ? $value::class : $value);

        foreach ($aliases = $this->getAliases($metadata, $id) as $alias) {
            $this->aliases[$alias] ??= [];
            $this->classes[$alias] ??= [];
            
            if ($objectHash && !in_array($objectHash, $this->aliases[$alias]))
                $this->aliases[$alias][] = $objectHash;

            if ($class && !in_array($class, $this->classes[$alias]))
                $this->classes[$alias][] = $class;
        }

        return $aliases;
    }

    public function get(string $id)
    {
        $alias = match(true) {
            !empty($this->aliases[$id] ?? []) => $this->aliases[$id],
            !empty($this->classes[$id] ?? []) => $this->classes[$id],
            default => throw new NotFoundContainerException($id)
        };

        return array_map(fn($h) => $this->objects[$h] ?? $h, $alias);
    }

    public function has(string $id)
    {
        return !empty($this->aliases[$id] ?? []) || !empty($this->classes[$id] ?? []);
    }

    public function addFactory(string $id, MethodMetadataInterface $factory): array
    {
        $class = $factory->getType() ?? throw new ContainerException("factory {$factory->getClass()}::{$factory->getName()} should have a return type");

        if (is_array($class)) {
            throw new ContainerException("factory {$factory->getClass()}::{$factory->getName()} shoudn't have a composed return type");
        }

        $metadata = $this->metadataFactory->getMetadataForClass($class);

        foreach ($aliases = $this->getAliases($metadata, $id) as $alias) {
            if (isset($this->factories[$alias])) {
                throw new ContainerException("type {$metadata->getName()} already have a factory");
            }

            $this->factories[$alias] = $factory;
        }

        return $aliases;
    }

    public function getFactory(string $id): MethodMetadataInterface
    {
        return $this->factories[$id] ?? throw new NotFoundContainerException($id);
    }

    public function hasFactory(string $id): bool
    {
        return isset($this->factories[$id]);
    }

    private function getAliases(ClassMetadataInterface $metadata, string $id)
    {
        return array_unique([
            ...$metadata->getHierarchy(),
            ...$metadata->getInterfaces(),
            $metadata->getName(),
            $id
        ]);
    }
    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable 
    {
        foreach ($this->objects as $object) {
            yield $object::class => $object;
        }

        foreach ($this->addedClasses as $class) {
            yield $class => $class;
        }
    }
}
