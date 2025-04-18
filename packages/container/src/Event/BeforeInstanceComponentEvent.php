<?php

namespace Primavera\Container\Event;

use Primavera\Container\Container;
use Primavera\Metadata\ParamMetadata;

class BeforeInstanceComponentEvent extends ComponentEvent 
{
    public function __construct(
        Container $container,
        object | string $component,
        public readonly array $dependencies = [],
        /**
         * if its a dependency for another class, this will be the ctor param metadata for it
         */
        public readonly ?ParamMetadata $paramMetadata = null,
    ) {
        parent::__construct($container, $component);
    }
}
