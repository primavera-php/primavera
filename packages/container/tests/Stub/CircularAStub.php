<?php

namespace Primavera\Container\Test\Stub;

class CircularAStub
{
    public function __construct(CircularBStub $circularBStub) {}
}

class CircularBStub
{
    public function __construct(CircularAStub $circularAStub) {}
}
