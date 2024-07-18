<?php


namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Get
{
    public function __construct(
        public readonly string $path = '/'
    ) {}
}
