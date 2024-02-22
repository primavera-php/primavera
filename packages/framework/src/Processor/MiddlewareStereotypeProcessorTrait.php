<?php

namespace Primavera\Framework\Processor;

use Slim\App;

trait MiddlewareStereotypeProcessorTrait
{
    public function process($stereotype)
    {
        /* @var $app App */
        $app = $this->getContainer()->get(App::class);

        $app->add($stereotype);
    }
}