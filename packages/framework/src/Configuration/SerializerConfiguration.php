<?php

namespace Primavera\Framework\Configuration;

use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\Annotation\Configuration;
use Primavera\Data\ObjectExtractor;
use Primavera\Data\ObjectHydrator;
use Primavera\Data\Serializer;

#[Configuration]
class SerializerConfiguration
{
    #[Factory]
    public function objectHydrator(MetadataFactoryInterface $mf): ObjectHydrator
    {
        return new ObjectHydrator($mf);
    }

    #[Factory]
    public function objectExtractor(MetadataFactoryInterface $mf): ObjectExtractor
    {
        return new ObjectExtractor($mf);
    }

    #[Factory]
    public function serializer(ObjectExtractor $extractor, ObjectHydrator $hydrator): Serializer
    {
        return new Serializer($extractor, $hydrator);
    }
}
