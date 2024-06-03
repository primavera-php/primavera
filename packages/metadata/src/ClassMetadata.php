<?php

namespace Primavera\Metadata;

class ClassMetadata implements ClassMetadataInterface
{
    use AnnotationsTrait;

    use ResolveTypeTrait {
        getType as protected;
        isDecoratedType as protected;
        isNativeType as protected;
        getParsedType as protected;
    }
    
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
        $this->fileResources = [$class->getFileName()];
        $this->resolveType();
    }

    public function getReflection(): \ReflectionClass
    {
        return $this->reflection ??= new \ReflectionClass($this->name);
    }

    private function getReflectionType()
    {
        return null;
    }

    private function getDocBlockTypePrefix()
    {
        return 'extends';
    }

    public function addMethodMetadata(MethodMetadataInterface $methodMetadata)
    {
        $this->methodMetadata[$methodMetadata->getName()] = $methodMetadata;
    }

    public function addPropertyMetadata(PropertyMetadata $propertyMetadata)
    {
        $this->propertyMetadata[$propertyMetadata->name] = $propertyMetadata;
    }

    public function __serialize(): array
    {
        return [
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->annotations,
            $this->interfaces,
            $this->hierarchy,
            $this->type,
            $this->typeInfo,
        ];
    }

    public function __unserialize(array $data)
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
            $this->type,
            $this->typeInfo,
        ] = $data;
    }
 
    public function merge(ClassMetadataInterface $object): void
    {
        $this->methodMetadata = array_merge($object->getMethodMetadata(), $this->methodMetadata);
        $this->propertyMetadata = array_merge($object->getPropertyMetadata(), $this->propertyMetadata);
        $this->fileResources = array_merge($object->getFileResources(), $this->fileResources);
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

    public function isFresh(): bool
    {
        foreach ($this->fileResources as $file) {
            if ($this->createdAt < filemtime($file)) {
                return false;
            }
        }

        return true;
    }

    public function instanceOf(string $classOrInterfaceName): bool
    {
        return in_array($classOrInterfaceName, $this->hierarchy)
            || in_array($classOrInterfaceName, $this->interfaces)
            || $classOrInterfaceName === $this->name;
    }

    public function hasGenerics(): bool
    {
        return $this->isDecoratedType();
    }

    public function getGenericsInfo(): ?array
    {
        return $this->typeInfo;
    }
}
