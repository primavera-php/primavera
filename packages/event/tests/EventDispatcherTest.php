<?php

namespace Primavera\Event;

use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    public function testShoudDispatchEvent() 
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->registerListener(new TestListener());

        $dispatcher->dispatch($event = new TestEvent());

        $this->assertTrue($event->called);
    }

    public function testShouldSerializeAndUnserializeEventDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->registerListener(new TestListener());
        $dispatcher->on(AnotherTestEvent::class, fn($e) => $e->called = true);

        $data = serialize($dispatcher);
        $dispatcher = unserialize($data);

        $dispatcher->dispatch($event = new TestEvent());
        $dispatcher->dispatch($onEvent = new AnotherTestEvent());

        $this->assertTrue($event->called);
        $this->assertTrue($onEvent->called);
    }
}

class TestEvent 
{
    public bool $called = false;
}

class AnotherTestEvent 
{
    public bool $called = false;
}

class TestListener {
    public function __invoke(TestEvent $testEvent)
    {
        $testEvent->called = true;
    }
}