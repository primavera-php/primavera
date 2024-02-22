<?php

namespace Primavera\Http\Stereotype;

#[\Attribute(\Attribute::TARGET_METHOD)]
class RemoteFormat
{
    public function __construct(
        public string $format = 'json',
    ) {}
}