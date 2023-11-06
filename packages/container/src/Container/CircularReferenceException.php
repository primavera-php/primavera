<?php


namespace Primavera\Container\Container;

class CircularReferenceException extends ContainerException
{    
    public function __construct(
        private string $onClass,
    ) {
       parent::__construct(sprintf('circular dependency on class %s', $onClass));
    }
}
