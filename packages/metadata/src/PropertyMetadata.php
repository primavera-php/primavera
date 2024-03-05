<?php

namespace Primavera\Metadata;

class PropertyMetadata implements ClassComponentMetadataInterface, \Serializable
{
    use AnnotationsTrait;
    
    use ResolveTypeTrait { 
        resolveType as private resolvePropertyType; 
    }
    
    private \ReflectionProperty $reflection;

    public string $class;

    public string $name;

    public function __construct(
        \ReflectionProperty $property,
        public readonly ?MethodMetadata $getter,
        public readonly ?MethodMetadata $setter,
        public readonly ?MethodMetadata $adder,
    ) {
        $this->class = $property->class;
        $this->name = $property->name;
        $this->reflection = $property;
        $this->reflection->setAccessible(true);

        $this->resolveType();
    }

    private function resolveType()
    {
        $this->resolvePropertyType();

        if (!$this->type && $this->hasSetter()) {
            $this->type = $this->setter->params[0]?->type;
            $this->typeInfo = $this->setter->params[0]?->typeInfo;
        }
    }

    public function getReflection(): \ReflectionProperty
    {
        $this->reflection ??= new \ReflectionProperty($this->class, $this->name);
        $this->reflection->setAccessible(true);

        return $this->reflection;
    }

    private function getReflectionType()
    {
        return $this->getReflection()->getType();
    }

    private function getDocBlockTypePrefix()
    {
        return 'var';
    }

    public function hasSetter() 
    {
        return !empty($this->setter);
    }
    
    public function hasGetter() 
    {
        return !empty($this->getter);
    }

    public function hasAdder() 
    {
        return !empty($this->adder);
    }

    public function getValue(object $object)
    {
        return $this->getReflection()->getValue($object);
    }

    public function setValue(object $object, mixed $value): void
    {
        $this->getReflection()->setValue($object, $value);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->class,
            $this->name,
            $this->type,
            $this->typeInfo,
            $this->annotations,
            $this->getter,
            $this->setter,
        ]);
    }

    public function unserialize(string $data): void
    {
        [
            $this->class,
            $this->name,
            $this->type,
            $this->typeInfo,
            $this->annotations,
            $this->getter,
            $this->setter,
        ] = unserialize($data);
    }
}
