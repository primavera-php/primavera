<?php


namespace Primavera\Framework\Processor;


use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareInterfaceStereotypeProcessor extends AbstractStereotypeProcessor
{
    use MiddlewareStereotypeProcessorTrait;

    public function getStereotypeName(): string
    {
        return MiddlewareInterface::class;
    }
}