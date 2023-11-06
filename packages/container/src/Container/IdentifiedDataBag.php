<?php

namespace Primavera\Container\Container;

use Primavera\Metadata\ClassComponentMetadataInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\MetadataInterface;
use Primavera\Metadata\TypedComponentMetadataInterface;

/**
 * @template T
 */
class IdentifiedDataBag implements \ArrayAccess, \IteratorAggregate
{
    private array $aliases = [];

    /**
     * @var array<string, T>
     */
    private array $data = [];

    public function __construct(
        /** @var MetadataFactoryInterface<T> */
        private MetadataFactoryInterface $metadataFactory,
    ) {}
    
	public function offsetExists($offset): bool
    {
        return isset($this->aliases[$offset]) || isset($this->data[$offset]);
    }

    /** 
     * @return T
     */
	public function offsetGet($offset): mixed
    {
        if (isset($this->aliases[$offset])) {
            if (is_array($this->aliases[$offset]) && count($this->aliases[$offset]) > 1) {
                throw new ContainerException("this interface or id {$offset} is implemented more than once, please specify an id");
            }

            $id = is_array($this->aliases[$offset]) ? $this->aliases[$offset][0] : $this->aliases[$offset];

            return $this->data[$id];
        }

        if (!isset($this->data[$offset])) {
            throw new NotFoundContainerException($offset);
        }

        return $this->data[$offset];
    }

    /**
     * @var $offset string
     * @var $value T
     */
	public function offsetSet($offset, $value): void
    {
        if ($value instanceof ClassMetadataInterface) {
            $this->storeMetadata($offset, $value);
        } elseif ($value instanceof ClassComponentMetadataInterface) {
            $this->storeMethodMetadata($offset, $value);
        } elseif (is_object($value)) {
            $this->storeObject($offset, $value);
        } elseif (is_scalar($value) || is_array($value)) {
            $this->storeData($offset, $value);
        } else {
            throw new ContainerException("cannot store this kind of data on the container: " . gettype($value));
        }
    }

    private function storeMetadata(string $offset, ClassMetadataInterface $metadata)
    {
        $id = $metadata->getName();

        $this->validateIsInterface($metadata);

        $this->data[$id] = $metadata;

        if ($offset !== $id) {
            $this->addAlias($offset, $id);
        }

        foreach ($this->getInterfaces($metadata) as $interface) {
            $this->addAlias($interface, $id);
        }
    }

    private function storeMethodMetadata(string $offset, ClassComponentMetadataInterface $methodMetadata)
    {
        if (!($type = $methodMetadata->getType())) {
            throw new ContainerException(
                "factory methods such as {$methodMetadata->getClass()}::{$methodMetadata->getName()} must have a return type"
            );
        }

        if (is_array($type)) {
            throw new ContainerException(
                "Please indicate a concrete class on return type of a factory, "
                ."union and intersection types isnt supported on {$methodMetadata->getClass()}::{$methodMetadata->getName()}"
            );
        }

        $metadata = $this->metadataFactory->getMetadataForClass($type);

        $this->validateIsInterface(
            $metadata,
            "interface return type {$type} isnt supported on facotry {$methodMetadata->getClass()}::{$methodMetadata->getName()}"
        );

        $id = $metadata->getName();
        $this->data[$id] = $methodMetadata;
        $this->addAlias($methodMetadata->getName(), $id);

        if ($offset !== $id) {
            $this->addAlias($offset, $id);
        }

        foreach ($this->getInterfaces($metadata) as $interface) {
            $this->addAlias($interface, $id);
        }
    }

    private function storeObject(string $offset, object $object)
    {
        $id = spl_object_hash($object);
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        $this->data[$id] = $object;
        $this->addAlias($metadata->getName(), $id);
        $this->addAlias($offset, $id);

        foreach ($this->getInterfaces($metadata) as $interface) {
            $this->addAlias($interface, $id);
        }
    }

    private function storeData(string $offset, mixed $data)
    {
        $this->data[$offset] = $data;
    }

    private function validateIsInterface(
        ClassMetadataInterface $metadata,
        $message = 'cannot register interfaces or abstract classes on the container',
    ) {
        if ($metadata->getReflection()->isInterface() || $metadata->getReflection()->isAbstract()) {
            throw new ContainerException($message);
        }
    }

	public function offsetUnset($offset): void 
    {
        throw new ContainerException('cannot unregister services from container');
    }

    private function addAlias($alias, $offset = null)
    {
        $this->aliases[$alias] ??= [];
        
        if ($offset && !in_array($offset, $this->aliases[$alias]))
            $this->aliases[$alias][] = $offset;
    }

    public function getInterfaces(ClassMetadataInterface $classMetadata)
    {
        $interfaces = $classMetadata->getInterfaces();
        $abstracts = array_filter(
            $classMetadata->getHierarchy(),
            fn($c) => $this->metadataFactory->getMetadataForClass($c)->getReflection()->isAbstract()
        );

        return array_unique([...$interfaces, ...$abstracts]);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * @param callable($metadata): bool $name
     * 
     * @return T[]
     */
    public function filter(callable $filter): array
    {
        return array_filter($this->data, $filter);
    }

    public function isPath(string $id): bool
    {
        return (bool) preg_match('/\./', $id);
    }
}
