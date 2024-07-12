<?php

namespace Primavera\Implementor\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Container\ContainerBuilder;
use Primavera\Implementor\Test\Stub\SumStub;

class InterfaceImplementorTest extends TestCase
{
    public function testShouldImplementMathInterface()
    {
        $cb = new ContainerBuilder();
        $cb->withNamespaces('Primavera\\Implementor\\Test\\');
        $container = $cb->build();

        $this->assertEquals(2, $container->get(SumStub::class)->sum(1, 1));
    }
}
