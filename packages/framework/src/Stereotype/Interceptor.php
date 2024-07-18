<?php


namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Interceptor
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
