<?php

namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class UseMiddleware
{
    /**
     * @var string
     */
    public $middlewareClass;

    public function __construct(string $middlewareClass = null)
    {
        $this->middlewareClass = $middlewareClass;
    }
}
