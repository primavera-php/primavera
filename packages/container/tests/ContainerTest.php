<?php

namespace Primavera\Container\Tests;

use PHPUnit\Framework\TestCase;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Transient;
use Primavera\Container\Factory\ContainerBuilder;

class ContainerTest extends TestCase
{
    private $container;

    public function setup(): void
    {
        $builder = new ContainerBuilder();
        $this->container = $builder->disableComponentScanner()
            ->withComponents(
                MyAnnotatedTransientClass::class,
                MyTransientConfig::class,
            )->build();
    }

    public function testShouldAlwaysCreateNewInstancesWhenTransient()
    {
        $container = $this->container;

        $this->assertNotSame($container->get(MyAnnotatedTransientClass::class), $container->get(MyAnnotatedTransientClass::class));
        $this->assertNotSame($container->get(MyTransientClass::class), $container->get(MyTransientClass::class));
        // $this->assertEquals(2, MyTransientConfig::$called);
    }
}


#[Transient]
class MyAnnotatedTransientClass
{

}

class MyTransientClass
{

}

#[Configuration]
class MyTransientConfig
{
    public static int $called = 0;

    #[Bean]
    #[Transient]
    public function transientClass(): MyTransientClass
    {
        self::$called++;

        return new MyTransientClass;
    }
}