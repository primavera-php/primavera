<?php

namespace Primavera\Serializer\Configuration;

use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\ContainerBuilderInterface;
use Primavera\Data\Formatter\JsonFormatter;
use Primavera\Data\ObjectExtractor;
use Primavera\Data\ObjectHydrator;
use Primavera\Data\Serializer;
use Primavera\Serializer\Processor\HydratorProcessor;
use Primavera\Serializer\Processor\SerializerFormatterProcessor;

class SerializerConfiguration
{
    #[Configurator]
    public static function configure(ContainerBuilderInterface $cb)
    {
        $cb->withComponents(
            ObjectHydrator::class,
            ObjectExtractor::class,
            HydratorProcessor::class,
            SerializerFormatterProcessor::class,
            JsonFormatter::class,
        );
    }

    #[Factory]
    public function serializer(ObjectHydrator $oh, ObjectExtractor $oe): Serializer
    {
        return new Serializer($oe, $oh);
    }
}
