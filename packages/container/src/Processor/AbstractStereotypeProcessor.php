<?php

namespace Primavera\Container\Processor;

use Primavera\Container\Container;
use Primavera\Container\ContainerAwareInterface;

abstract class AbstractStereotypeProcessor implements ContainerAwareInterface
{
    private Container $container;
    
    public function setContainer(Container $container): void {
        $this->container = $container;
    }

    public function getContainer(): Container {
        return $this->container;
    }

    protected function fetchComponents() {
        return $this->container->getComponentsByStereotype($this->getStereotypeName());
    }
    
    abstract public function getStereotypeName(): string;
    
    public function findAndProcess() {
        foreach ($this->fetchComponents() as $stereotype) {
            $this->process($stereotype);
        }
    }
    
    abstract public function process($stereotype);
}
