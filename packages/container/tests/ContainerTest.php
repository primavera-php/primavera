<?php

namespace Primavera\Container\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Container\Container;
use Primavera\Container\Event\BeforeInstanceComponentEvent;
use Primavera\Container\Exception\ContainerException;
use Primavera\Container\Test\Stub\BarStub;
use Primavera\Container\Test\Stub\ClassFactory;
use Primavera\Container\Test\Stub\FooInterface;
use Primavera\Container\Test\Stub\FooStub;
use Primavera\Container\Test\Stub\CircularAStub;
use Primavera\Container\Test\Stub\FromFactory;
use Primavera\Container\Test\Stub\NoCtorStub;
use Primavera\Event\EventDispatcherInterface;
use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Metadata\Factory\MetadataFactoryInterface;

class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container(
            (new MetadataFactoryFactory)->createAnnotationMetadataFactory()
        );
    }

    public function testShouldGetAndSetServices()
    {
        $container = $this->container;

        $container->set('foo', new FooStub('foo', 'bar'));

        $this->assertInstanceOf(FooStub::class, $container->get('foo'));
        $this->assertInstanceOf(FooInterface::class, $container->get('foo'));
        $this->assertInstanceOf(FooInterface::class, $container->get(FooInterface::class));
        $this->assertInstanceOf(FooInterface::class, $container->get(FooStub::class));
    }

    public function testShouldCreateAndResolveDependencies()
    {
        $container = $this->container;

        $container->set('foo', 'foo');
        $container->set('bar', 'bar');
        $container->set('foo_stub', FooStub::class);

        $instance = $container->get(BarStub::class);

        $this->assertEquals($instance, $container->get(BarStub::class));
        $this->assertEquals('foo', $instance->foo->foo);
        $this->assertEquals('bar', $instance->foo->bar);
    }

    public function testShouldDetectCircularReference()
    {
        $this->expectException(ContainerException::class);

        $this->container->get(CircularAStub::class);
    }

    public function testShouldGetCompnentWihtoutConstructor()
    {
        $this->assertNotEmpty($this->container->get(NoCtorStub::class));
    }

    public function testShouldInstanceFromFactory()
    {
        $factory = $this->container
            ->get(MetadataFactoryInterface::class)
            ->getMetadataForClass(ClassFactory::class)
            ->getMethodMetadata('factory');
        
        $this->container->set($factory->getType(), $factory);
        $this->container->set(ClassFactory::class, $cf = new ClassFactory());

        $this->assertInstanceOf(FromFactory::class, $this->container->get(FromFactory::class));
        $this->assertTrue($cf->called);
    }

    public function testShouldInterceptInstance()
    {
        $this->container->get(EventDispatcherInterface::class)
            ->registerListener(fn(BeforeInstanceComponentEvent $e) => $e->result = new \stdClass);

        $this->assertInstanceOf(\stdClass::class, $this->container->get(FooStub::class));
    }
}