<?php


namespace Primavera\Http\Stereotype;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
class RequestBody
{
    /**
     * @var string
     */
    public $argName = null;

    /**
     * @var string
     */
    public $type = null;


    /**
     * @var string
     */
    public $format = 'json';

    /**
     * @param string|null $argName
     * @param string|null $type
     * @param string|null $format
     */
    public function __construct(string $argName = null, string $type = null, string $format = 'json')
    {
        $this->argName = $argName;
        $this->type = $type;
        $this->format = $format;
    }
}
