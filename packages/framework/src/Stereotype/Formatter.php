<?php


namespace Primavera\Framework\Stereotype;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Formatter
{
    /**
     * @var string
     */
    public $format = null;

    public function __construct(string $format = null)
    {
        $this->format = $format;
    }
}
