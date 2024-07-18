<?php

namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
class RequestBody
{
    public function __construct(
        public readonly ?string $argName = null,
        public readonly ?string $type = null,
        public readonly string $format = 'json'
    ) {}
}
