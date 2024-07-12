<?php

namespace Primavera\Serializer\Processor;

use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Primavera\Data\Formatter\FormatAwareInterface;
use Primavera\Data\SerializerInterface;
use Primavera\Framework\Stereotype\Formatter;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Psr\Container\ContainerInterface;

class SerializerFormatterProcessor implements ComponentPostProcessorInterface
{
    public function __construct(
        private MetadataFactoryInterface $mf,
        private SerializerInterface $serializer,
    ) {}

    public function process(object $component, ContainerInterface $container)
    {
        $this->serializer->registerFormat($component);
    }
    
    public function canProcess(object $component): bool
    {
        return $this->mf->getMetadataForClass($component::class)->hasAnnotation(Formatter::class)
            || $component instanceof FormatAwareInterface;
    }
}
