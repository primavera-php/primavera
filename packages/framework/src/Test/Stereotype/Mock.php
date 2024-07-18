<?php

namespace Primavera\Framework\Test\Stereotype;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Mock
{
    public $type;

    public $serviceId;

    public function __construct(string $type, string $serviceId = null)
    {
        $this->type = $type;
        $this->serviceId = $serviceId;
    }
}
