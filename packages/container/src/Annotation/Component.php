<?php

namespace Primavera\Container\Annotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Component 
{
    public $name;

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }
}
