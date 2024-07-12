<?php

namespace Primavera\Container\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Container\ContainerBuilder;
use Primavera\Container\Test\Stub\BarStub;
use Primavera\Container\Test\Stub\FromFactory;
use Primavera\Container\Test\Stub\FromFactoryInterface;
use Primavera\Container\Test\Stub\ImplementableInterface;
use Primavera\Container\Test\Stub\ImportedStub;
use Primavera\Container\Test\Stub\ImportedStubInterface;
use Primavera\Container\Test\Stub\InjectableStubInterface;
use Primavera\Container\Test\Stub\InterfaceImplementor;

class ContainerBuilderTest extends TestCase
{
    public function testShouldBuildContainer()
    {
        $cb = new ContainerBuilder();
        $cb->withConfigFile(__DIR__ . '/Stub/config.yaml')
            ->withNamespaces('Primavera\\Container\\Test\\')
            ->withPreProcessors(new InterfaceImplementor);

        $container = $cb->build();

        $barStub = $container->get(BarStub::class);
        $wired = $container->get(InjectableStubInterface::class);

        $this->assertEquals('foo', $barStub->foo->foo);
        $this->assertEquals('foo', $wired->foo);
        $this->assertInstanceOf(BarStub::class, $wired->barStub);
        $this->assertInstanceOf(FromFactory::class, $container->get(FromFactoryInterface::class));
        $this->assertInstanceOf(ImportedStub::class, $container->get(ImportedStubInterface::class));
        $this->assertInstanceOf('Implemented', $container->get(ImplementableInterface::class));
        $this->assertEquals(2, $container->get(ImplementableInterface::class)->sum(1, 1));
    }
}
