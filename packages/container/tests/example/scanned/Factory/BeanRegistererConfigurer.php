<?php


namespace ScannedTest\Factory;


use Primavera\Container\Bean\BeanRegisterer;
use Primavera\Container\Bean\BeanRegistererConfiguratorInterface;

class BeanRegistererConfigurer implements BeanRegistererConfiguratorInterface
{
    public function configure(BeanRegisterer $beanRegisterer)
    {
        $beanRegisterer->addNamespace('ScannedTest\\')
            ->addComponent(SomeRegisteredTestComponent::class)
            ->addStereotype(SomeTestBehavior::class)
        ;
    }
}

class SomeRegisteredTestComponent {
    public function getName() {
        return 'test component';
    }
}