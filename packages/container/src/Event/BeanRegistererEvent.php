<?php

namespace Primavera\Container\Event;

use Primavera\Container\Bean\BeanRegisterer;
use Primavera\Container\Container;

class BeanRegistererEvent 
{
    private Container $container;
    
    private BeanRegisterer $beanRegisterer;
    
    public function __construct(Container $container, BeanRegisterer $beanRegisterer) {
        $this->container = $container;
        $this->beanRegisterer = $beanRegisterer;
    }
}
