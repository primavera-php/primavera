<?php


namespace Primavera\Framework\Stereotype;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller
{
    /**
     * @var string
     */
    public $path = '/';

    public function __construct(string $path = '/')
    {
        $this->path = $path;
    }
}
