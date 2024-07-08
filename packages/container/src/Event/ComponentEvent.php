<?php

namespace Primavera\Container\Event;

use Primavera\Container\Container;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @template T
 */
class ComponentEvent implements StoppableEventInterface
{
    private bool $stoped = false;

    public $result = null;

    /**
     * @param T | class-string<T> $component
     */
    public function __construct(
        public Container $container,
        public object | string $component,
    ) {}

    public function stopPropagation() 
    {
        $this->stoped = true;
    }

    public function isPropagationStopped(): bool 
    {
        return $this->stoped;
    }
}
