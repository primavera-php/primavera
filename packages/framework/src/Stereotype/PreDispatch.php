<?php

namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PreDispatch
{
    /**
     * @var int
     */
    public $priority;

    public function __construct(int $priority = null)
    {
        $this->priority = $priority;
    }
}
