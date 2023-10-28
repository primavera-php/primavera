<?php

namespace Vox\Metadata;

use Metadata\MethodMetadata as BaseMetadata;

class MethodMetadata implements MethodMetadataInterface, \Serializable
{
    use AnnotationsTrait, ResolveTypeTrait, ResolveParamsTrait;
    
    private \ReflectionMethod $reflection;

    public string $class;

    public string $name;

    public function __construct(\ReflectionMethod $method)
    {
        $this->class = $method->class;
        $this->name = $method->name;
        $this->reflection = $method;
        $this->reflection->setAccessible(true);
        $this->resolveType();
        $this->resolveParams();
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function getReflectionType()
    {
        return $this->getReflection()->getReturnType();
    }

    private function getDocBlockTypePrefix()
    {
        return 'return';
    }

    public function getReflection(): \ReflectionMethod
    {
        return $this->reflection ??= new \ReflectionMethod($this->class, $this->name);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function invoke(...$args): mixed
    {
        return $this->getReflection()->invoke(...$args);
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->class, 
            $this->name,
            $this->annotations,
            $this->type,
            $this->typeInfo,
            $this->params,
        ]);
    }

    public function unserialize(string $data)
    {
        [
            $this->class, 
            $this->name,
            $this->annotations,
            $this->type,
            $this->typeInfo,
            $this->params,
        ] = unserialize($data);
    }
}
