<?php


namespace Primavera\Container\Bean;


interface BeanRegistererConfiguratorInterface
{
    public function configure(BeanRegisterer $beanRegisterer);
}