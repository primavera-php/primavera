<?php


namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class HttpClient
{
    public function __construct(
        public readonly string $uri
    ) {}
}
