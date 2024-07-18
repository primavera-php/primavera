<?php

namespace Primavera\Framework\Processor;

use Psr\Container\ContainerInterface;
use Slim\App;

trait MiddlewareStereotypeProcessorTrait
{
    public function process(object $stereotype, ContainerInterface $container)
    {
        $app = $container->get(App::class);

        $app->add($stereotype);
    }
}