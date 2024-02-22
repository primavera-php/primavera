<?php


namespace Primavera\Framework\Processor;


use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Primavera\Framework\Stereotype\Middleware;

class MiddlewareStereotypeProcessor extends AbstractStereotypeProcessor
{
    use MiddlewareStereotypeProcessorTrait;

    public function getStereotypeName(): string
    {
        return Middleware::class;
    }
}