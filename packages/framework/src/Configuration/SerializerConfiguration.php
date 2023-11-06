<?php

namespace Vox\Framework\Configuration;

use Vox\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Configuration;
use Vox\Data\ObjectExtractor;
use Vox\Data\ObjectExtractorInterface;
use Vox\Data\ObjectHydrator;
use Vox\Data\ObjectHydratorInterface;
use Vox\Data\Serializer;

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
    public function serializer(ObjectExtractorInterface $extractor, ObjectHydratorInterface $hydrator): Serializer
    {
        return new Serializer($extractor, $hydrator);
    }
}
