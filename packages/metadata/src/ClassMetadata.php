<?php

namespace Vox\Metadata;

class ClassMetadata implements ClassMetadataInterface
{
    use AnnotationsTrait;
    
    private \ReflectionClass $reflection;

    public string $name;

    /**
     * @var MethodMetadata[]
     */
    public array $methodMetadata = [];

    /**
     * @var PropertyMetadata[]
     */
    public array $propertyMetadata = [];

    /**
     * @var string[]
     */
    public array $fileResources = [];

    /**
     * @var string[]
     */
    public array $interfaces;

    public array $hierarchy = [];

    public int $createdAt;

    public function __construct(\ReflectionClass $class)
    {
        $this->name = $class->name;
        $this->reflection = $class;
        $this->createdAt = time();
        $this->interfaces = $class->getInterfaceNames();
    }

    public function getReflection(): \ReflectionClass
    {
        return $this->reflection ??= new \ReflectionClass($this->name);
    }

    public function addMethodMetadata(MethodMetadataInterface $methodMetadata)
    {
        $this->methodMetadata[$methodMetadata->getName()] = $methodMetadata;
    }

    public function addPropertyMetadata(PropertyMetadata $propertyMetadata)
    {
        $this->propertyMetadata[$propertyMetadata->name] = $propertyMetadata;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->annotations,
            $this->interfaces,
            $this->hierarchy,
        ]);
    }

    public function unserialize(string $data)
    {
        [
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->annotations,
            $this->interfaces,
            $this->hierarchy,
        ] = unserialize($data);
    }
 
    public function merge(ClassMetadataInterface $object): void
    {
        $this->methodMetadata = array_merge($object->getMethodMetadata(), $this->methodMetadata);
        $this->propertyMetadata = array_merge($object->getPropertyMetadata(), $this->propertyMetadata);
        $this->fileResources = array_merge( $object->getFileResources(), $this->fileResources);
        $this->annotations = array_merge($object->getAnnotations(), $this->annotations);
        $this->hierarchy = [...$object->getHierarchy(), $object->getName()];

        if ($object->getCreatedAt() < $this->createdAt) {
            $this->createdAt = $object->getCreatedAt();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return MethodMetadata[]
     */
    public function getMethodMetadata(): array
    {
        return $this->methodMetadata;
    }

    /**
     * @return PropertyMetadata[]
     */
    public function getPropertyMetadata(): array
    {
        return $this->propertyMetadata;
    }

    /**
     * @return string[]
     */
    public function getFileResources(): array
    {
        return $this->fileResources;
    }

    /**
     * @return string[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return string[]
     */
    public function getHierarchy(): array
    {
        return $this->hierarchy;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
}
