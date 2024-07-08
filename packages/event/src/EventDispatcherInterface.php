<?php

namespace Primavera\Event;

use Psr\EventDispatcher\EventDispatcherInterface as BaseInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface EventDispatcherInterface extends BaseInterface, ListenerProviderInterface
{
    public function registerListener(callable $listener);

    public function on(string $type, callable $listener);
}
