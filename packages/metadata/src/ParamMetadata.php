<?php

namespace Primavera\Metadata;
use Error;

class ParamMetadata implements TypedComponentMetadataInterface, \Serializable
{
    use ResolveTypeTrait, AnnotationsTrait;

    public ?string $class;
    public string $function;
    public string $name;

    public function __construct(
        private \ReflectionParameter $reflection,
        private MethodMetadata | FunctionMetadata $metadata,
    ) {
        $this->class = $metadata instanceof MethodMetadata ? $metadata->class : null;
        $this->function = $metadata->name;
        $this->name = $reflection->name;

        $this->resolveType();
        $this->loadAnnotations();
    }

    public function getReflection(): \ReflectionParameter
    {
        return $this->reflection ??= new \ReflectionParameter(
            $this->class 
                ? [$this->class, $this->function] 
                : $this->function,
            $this->name
        );
    }

    public function getReflectionType()
    {
        return $this->getReflection()->getType();
    }

    public function getDocComment()
    {
        return $this->metadata->getDocComment();
    }

    private function getDocBlockTypePrefix()
    {
        return "var\s+\${$this->name}";
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function unserialize(string $data)
    {
        [
            $this->class,
            $this->function,
            $this->name,
            $this->type,
            $this->typeInfo,
            $this->annotations,
            $this->metadata,
        ] = unserialize($data);
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->class,
            $this->function,
            $this->name,
            $this->type,
            $this->typeInfo,
            $this->annotations,
            $this->metadata,
        ]);
    }

    public function loadAnnotations()
    {
        foreach ($this->reflection->getAttributes() as $attribute) {
            try {
                $this->annotations[$attribute->getName()] = $attribute->newInstance();
            } catch (Error $e) {
                // catch all
            }
        }
    }
}
