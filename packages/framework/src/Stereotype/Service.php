<?php

namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Service
{
    /**
     * @var string
     */
    public $beanName;

    public function __construct(string $beanName = null)
    {
        $this->beanName = $beanName;
    }
}
