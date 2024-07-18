<?php

namespace Primavera\Container\Event;

use IteratorAggregate;
use Primavera\Event\StopableEventTrait;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class AfterContainerBuiltEvent implements StoppableEventInterface
{
    use StopableEventTrait;

    public function __construct(
        public ContainerInterface & IteratorAggregate $container,
    ) {}
}
