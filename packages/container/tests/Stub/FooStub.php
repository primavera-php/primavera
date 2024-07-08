<?php

namespace Primavera\Container\Test\Stub;

use Primavera\Container\Annotation\Component;

#[Component]
class FooStub implements FooInterface
{
    public function __construct(
        public string $foo,
        public string $bar,
    ) {}

    
    public function getBar() 
    {
        return $this->bar;
    }
    
    public function getFoo() 
    {
        return $this->foo;
    }
}
