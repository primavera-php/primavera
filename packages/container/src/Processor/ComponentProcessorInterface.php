<?php

namespace Primavera\Container\Processor;

use Psr\Container\ContainerInterface;

/**
 * @template T
 */
interface ComponentProcessorInterface
{
    /**
     * @param T $component
     * 
     * @return bool
     */
    public function canProcess(object $component): bool;
}
