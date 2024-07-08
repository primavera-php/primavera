<?php

namespace Primavera\Container\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Factory 
{
    /**
     * @var string
     */
    public $name;

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }
}
