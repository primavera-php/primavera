<?php


namespace Primavera\Framework\Stereotype;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @NamedArgumentConstructor
 */
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
