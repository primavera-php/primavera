<?php

namespace Primavera\Container\Test\Stub;

use Primavera\Container\ContainerBuilderInterface;
use Primavera\Container\Processor\ComponentPreProcessorInterface;
use Primavera\Metadata\ClassMetadataInterface;

class InterfaceImplementor implements ComponentPreProcessorInterface
{
    public function canProcess(object $component): bool 
    {
        return $component->getName() == ImplementableInterface::class;
    }

    public function process(ClassMetadataInterface $component, ContainerBuilderInterface $containerBuilder) 
    {
        $type = 'class';

        $class = "
            $type Implemented implements \Primavera\Container\Test\Stub\ImplementableInterface {
                public function sum(\$a, \$b) {
                    return \$a + \$b;
                }
            }
        ";

        eval($class);

        $containerBuilder->withComponents('Implemented');
    }
    
}
