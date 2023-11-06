<?php


namespace Primavera\Framework\Stereotype;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Put
{
    /**
     * @var string
     * @required
     */
    public $path = '{id}';

    public function __construct(string $path = '{id}')
    {
        $this->path = $path;
    }
}
