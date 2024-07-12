<?php

namespace Primavera\Container\Processor;

use Primavera\Container\ContainerBuilderInterface;
use Primavera\Metadata\ClassMetadataInterface;

/**
 * @extends ComponentProcessorInterface<ClassMetadataInterface>
 */
interface ComponentPreProcessorInterface extends ComponentProcessorInterface
{
    public function process(ClassMetadataInterface $component, ContainerBuilderInterface $containerBuilder);
}
