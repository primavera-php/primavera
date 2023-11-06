<?php

namespace Primavera\Metadata;

class FunctionMetadata implements MetadataInterface, TypedComponentMetadataInterface, \Serializable
{
    use AnnotationsTrait, ResolveTypeTrait, ResolveParamsTrait;

    public ?string $name;
    
    private \ReflectionFunction $reflection;

    public function __construct(\ReflectionFunction $function)
    {
        $this->reflection = $function;
        $this->name = $function->name;
        $this->resolveType();
        $this->resolveParams();
    }
    
    private function getReflectionType()
    {
        return $this->getReflection()->getReturnType();
    }

    private function getDocBlockTypePrefix()
    {
        return 'return';
    }

    public function getReflection(): \ReflectionFunction
    {
        return $this->reflection ??= new \ReflectionFunction($this->name);
    }

    public function invoke(...$args)
    {
        $this->getReflection()->invoke(...$args);
    }

    public function isAnonymous(): bool
    {
        return isset($this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function serialize(): ?string
    {
        return serialize([
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
            $this->name,
            $this->annotations,
            $this->type,
            $this->typeInfo,
            $this->params,
        ] = unserialize($data);
    }
}
