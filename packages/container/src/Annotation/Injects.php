<?php

namespace Primavera\Container\Annotation;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Injects
{
    public string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
