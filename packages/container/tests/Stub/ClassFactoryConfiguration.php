<?php

namespace Primavera\Container\Test\Stub;

use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\ContainerBuilderInterface;
use Primavera\Container\Test\Stub\Annotation\ImportStubs;

#[Configuration]
#[ImportStubs]
class ClassFactoryConfiguration
{
    public bool $called = false;

    #[Configurator]
    public static function configure(ContainerBuilderInterface $cb)
    {
        $cb->withComponents(InjectableStub::class);
    }

    #[Factory]
    public function factory(): FromFactory
    {
        $this->called = true;
        
        return new FromFactory;
    }
}
