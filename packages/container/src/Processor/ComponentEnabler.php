<?php

namespace Primavera\Container\Processor;

use Primavera\Container\ContainerBuilderInterface;
use Primavera\Metadata\ClassMetadataInterface;

class ComponentEnabler implements ComponentPreProcessorInterface
{
    /**
     * @param class-string $componentClass
     */
    public function __construct(
        private string $componentClass,
    ) {}

    public function process(ClassMetadataInterface $component, ContainerBuilderInterface $containerBuilder)
    {
        $containerBuilder->withComponents($component->getName());
    }
    
    public function canProcess(object $component): bool 
    {
        return $component->instanceOf($this->componentClass) || $component->hasAnnotation($this->componentClass);
    }
}
