<?php

namespace Primavera\Serializer\Processor;

use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Primavera\Data\SerializerInterface;
use Primavera\Data\TypeAwareObjectExtractor;
use Primavera\Data\TypeAwareObjectHydrator;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Psr\Container\ContainerInterface;

class HydratorProcessor implements ComponentPostProcessorInterface
{
    public function __construct(
        private MetadataFactoryInterface $mf,
        private SerializerInterface $serializer,
    ) {}

    public function process(object $component, ContainerInterface $container)
    {
        switch (gettype($component)) {
            case TypeAwareObjectExtractor::class:
                $this->serializer->registerCustomExtractor($component);
                break;
            case TypeAwareObjectHydrator::class:
                $this->serializer->registerCustomHydrator($component);
                break;
        }
    }
    
    public function canProcess(object $component): bool
    {
        return $component instanceof TypeAwareObjectHydrator
            || $component instanceof TypeAwareObjectExtractor;
    }
}
