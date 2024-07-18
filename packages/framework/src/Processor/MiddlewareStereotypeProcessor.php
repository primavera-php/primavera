<?php


namespace Primavera\Framework\Processor;


use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Primavera\Framework\Stereotype\Middleware;
use Primavera\Metadata\Factory\MetadataFactoryInterface;

class MiddlewareStereotypeProcessor implements ComponentPostProcessorInterface
{
    use MiddlewareStereotypeProcessorTrait;

    public function __construct(
        private MetadataFactoryInterface $mf,
    ) {}

    public function canProcess(object $component): bool
    {
        return $this->mf->getMetadataForClass($component::class)->hasAnnotation(Middleware::class);
    }
}