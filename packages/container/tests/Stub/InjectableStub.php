<?php

namespace Primavera\Container\Test\Stub;

use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Annotation\Value;

interface InjectableStubInterface {}

class InjectableStub implements InjectableStubInterface
{
    #[Autowired]
    public BarStub $barStub;

    #[Value]
    public string $foo;
}
