<?php

namespace Primavera\Container\Event;

use Primavera\Container\Container;
use Primavera\Event\StopableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @template T
 */
class ComponentEvent implements StoppableEventInterface
{
    use StopableEventTrait;
    
    public $result = null;

    /**
     * @param T | class-string<T> $component
     */
    public function __construct(
        public Container $container,
        public object | string $component,
    ) {}
}
