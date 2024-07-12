<?php

namespace Primavera\Implementor\Test\Stub\Configuration;

use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\ContainerBuilder;
use Primavera\Implementor\Test\Stub\Implementor\MathImplementor;
use Primavera\Serializer\Annotation\EnableSerializer;

#[Configuration]
#[EnableSerializer]
class ImplementorConfiguration
{
    #[Configurator]
    public static function configure(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->withPreProcessors(new MathImplementor());
    }
}
