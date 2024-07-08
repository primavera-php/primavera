<?php

namespace Primavera\Container\Annotation;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Value
{
    public function __construct(
        public ?string $id = null,
        public ?string $defaultValue = null,
    ) {}
}
