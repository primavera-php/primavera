<?php

namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ErrorHandler 
{
    public function __construct(
        public readonly int $priority = 1,
    ) {}
}
