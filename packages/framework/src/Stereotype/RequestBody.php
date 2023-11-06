<?php


namespace Primavera\Framework\Stereotype;

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
     * @param string|null $argName
     * @param string|null $type
     */
    public function __construct(string $argName = null, string $type = null)
    {
        $this->argName = $argName;
        $this->type = $type;
    }
}
