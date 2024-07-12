<?php

namespace Primavera\Http\Configuration;

use Primavera\Container\Annotation\Configurator;
use Primavera\Container\ContainerBuilder;
use Primavera\Http\Processor\HttpClientProcessor;
use Primavera\Serializer\Annotation\EnableSerializer;

#[EnableSerializer]
class HttpConfiguration
{
    #[Configurator]
    public static function configure(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->withPreProcessors(new HttpClientProcessor());
    }
}
