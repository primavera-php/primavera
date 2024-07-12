<?php

namespace Primavera\Container\Processor;

use Primavera\Metadata\ClassMetadataInterface;
use Psr\Container\ContainerInterface;

/**
 * @extends ComponentProcessorInterface<object>
 */
interface ComponentPostProcessorInterface extends ComponentProcessorInterface
{
    public function process(object $component, ContainerInterface $container);
}
