<?php

namespace Primavera\Container\Test\Stub;

class BarStub
{
    public function __construct(
        public FooInterface $foo,
    ) {}
}
