<?php

namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Header
{
    public function __construct(
        public string $name,
    ) {}
}