<?php

namespace Primavera\Implementor\Test\Stub;

use Primavera\Implementor\Test\Stub\Annotation\Math;

#[Math]
interface SumStub
{
    public function sum($a, $b): int;
}
