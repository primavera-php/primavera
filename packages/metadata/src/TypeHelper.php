<?php

namespace Primavera\Metadata;

class TypeHelper
{
    use ResolveTypeTrait;

    private $reflection;

    private object | string | null $class;

    public function __construct(string $type, object | string $class = null)
    {
        $this->type = $type;
        $this->class = $class;
        $this->typeInfo = $this->parseTypeDecoration($type);

        if ($this->typeInfo && !$this->class) {
            $this->class = $this->typeInfo['decoration'];
        }
    }

    private function getReflectionType() 
    {
        return $this->getReflection()->name;
    }

    private function getDocBlockTypePrefix() {}

    public function getDocComment() {}

    public function getReflection(): \ReflectionClass
    {
        return $this->reflection ??= new \ReflectionClass($this->class ?? $this->type);
    }
}