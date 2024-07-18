<?php


namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Patch
{
    public function __construct(
        public readonly string $path = '{id}'
    ) {}
}
