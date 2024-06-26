<?php

namespace Primavera\Framework\Configuration;

use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Configuration;
use Primavera\Data\ObjectExtractor;
use Primavera\Data\ObjectHydrator;
use Primavera\Data\Serializer;

#[Configuration]
class SerializerConfiguration
{
    #[Bean]
    public function objectHydrator(MetadataFactoryInterface $mf): ObjectHydrator
    {
        return new ObjectHydrator($mf);
    }

    #[Bean]
    public function objectExtractor(MetadataFactoryInterface $mf): ObjectExtractor
    {
        return new ObjectExtractor($mf);
    }

    #[Bean]
    public function serializer(ObjectExtractor $extractor, ObjectHydrator $hydrator): Serializer
    {
        return new Serializer($extractor, $hydrator);
    }
}
