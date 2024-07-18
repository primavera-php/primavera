<?php


namespace Primavera\Framework\Processor;

use Primavera\Container\Processor\ComponentPostProcessorInterface;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareInterfaceStereotypeProcessor implements ComponentPostProcessorInterface
{
    use MiddlewareStereotypeProcessorTrait;

    public function canProcess(object $component): bool
    {
        return $component instanceof MiddlewareInterface;
    }
}