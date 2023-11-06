<?php


namespace Vox\Framework\Processor;


use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Slim\App;
use Vox\Framework\Stereotype\Middleware;

class MiddlewareStereotypeProcessor extends AbstractStereotypeProcessor
{

    public function getStereotypeName(): string
    {
        return Middleware::class;
    }

    public function process($stereotype)
    {
        /* @var $app App */
        $app = $this->getContainer()->get(App::class);

        $app->add($stereotype);
    }
}