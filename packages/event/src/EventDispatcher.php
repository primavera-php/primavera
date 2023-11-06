<?php

namespace Primavera\Event;

use Opis\Closure\SerializableClosure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    /**
     * @var callable[object]
     */
    private array $listeners = [];
    
    public function dispatch(object $event) 
    {
        foreach ($this->getListenersForEvent($event) as $listener) {
            $listener($event);
        } 
    }

    public function getListenersForEvent(object $event): iterable 
    {
        return $this->listeners[get_class($event)] ?? [];
    }
    
    public function registerListener(callable $listener) 
    {
        // $listener = \Closure::fromCallable($listener);
        
        $reflectionMethod = new \ReflectionFunction($listener(...));
        
        $parameters = $reflectionMethod->getParameters();
        $parametersCount = count($parameters);
        
        if ($parametersCount > 1 || $parametersCount == 0) {
            throw new \BadMethodCallException("listener must have exactly 1 parameter {$parametersCount} detected");
        }
        
        $type = $parameters[0]->getType();

        if (!$type) {
            throw new \BadMethodCallException("listener parameter must be type hinted");
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            throw new \BadMethodCallException('a listener should have a type hint and intersection or union types are not supported');
        }

        if ($type->isBuiltin()) {
            throw new \BadMethodCallException('the type of a listener must be a class');
        }

        if (interface_exists($type->getName())) {
            throw new \BadMethodCallException('the type of a listener must be concrete, interfaces are not supported');
        }

        $type = $type->getName();

        $this->listeners[$type] ??= [];
        $this->listeners[$type][] = $listener;
    }

    public function on(string $type, callable $listener)
    {
        if (!class_exists($type) || interface_exists($type)) {
            throw new \BadMethodCallException('an event must be a concrete class');
        }

        $this->listeners[$type] ??= [];
        $this->listeners[$type][] = $listener;

        return $this;
    }

    public function __serialize()
    {
        $data = [];

        foreach (array_keys($this->listeners) as $type) {
            $data[$type] = array_map(
                fn($l) =>
                    is_object($l) && !$l instanceof \Closure 
                        ? serialize($l) 
                        : new SerializableClosure($l),
                $this->listeners[$type]
            );     
        }

        return $data;
    }

    public function __unserialize(array $data)
    {
        $this->listeners = [];

        foreach ($data as $type => $listeners) {
            $this->listeners[$type] = array_map(
                fn($l) => $l instanceof SerializableClosure ? $l->getClosure() : unserialize($l),
                $listeners
            );
        }
    }
}
