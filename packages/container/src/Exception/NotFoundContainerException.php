<?php

namespace Primavera\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundContainerException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct(string $id) 
    {
        parent::__construct("Component with id $id not registered");
    }
    
    public static function trigger(string $name) 
    {
        throw new self($name);
    }
}
