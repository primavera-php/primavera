<?php

namespace Primavera\Container\Processor;

use Psr\Container\ContainerInterface;

interface ComponentPostProcessorInterface
{
    public function canProcess(object $component): bool;

    public function process(object $component, ContainerInterface $container);
}
