<?php

namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Query
{
    public function __construct(
        public ?string $name = null,
    ) {}
}