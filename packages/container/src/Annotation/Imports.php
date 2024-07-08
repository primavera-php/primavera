<?php

namespace Primavera\Container\Annotation;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Imports
{
    public $configurations;

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }
}
