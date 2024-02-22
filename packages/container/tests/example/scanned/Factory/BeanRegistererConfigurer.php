<?php


namespace ScannedTest\Factory;

use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Bean\BeanRegisterer;

#[Configuration]
class BeanRegistererConfigurer
{
    #[Configurator]
    public static function configure(BeanRegisterer $beanRegisterer)
    {
        $beanRegisterer->addNamespace('ScannedTest\\')
            ->addComponent(SomeRegisteredTestComponent::class)
            ->addStereotype(SomeTestBehavior::class)
        ;
    }
}

class SomeRegisteredTestComponent 
{
    public function getName() 
    {
        return 'test component';
    }
}