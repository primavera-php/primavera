<?php

namespace Primavera\Container\Exception;

use Primavera\Metadata\ParamMetadataInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundContainerException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct(string $id, ParamMetadataInterface $paramMetadata = null)
    {
        $message = "Component with id $id not registered";

        if ($paramMetadata) {
            $message .= " for {$paramMetadata->getClass()}::{$paramMetadata->getName()}: {$paramMetadata->getType()}";
        }

        parent::__construct($message);
    }
    
    public static function trigger(string $name) 
    {
        throw new self($name);
    }
}
