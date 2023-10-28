<?php


namespace Vox\Framework\Container;


use PhpBeans\Container\Container;

trait ContainerAwareTrait
{
    private Container $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}