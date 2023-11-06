<?php


namespace Primavera\Framework\Test\Stereotype;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Mock
{
    /**
     * @var string
     * @required
     */
    public $type;

    /**
     * @var string
     */
    public $serviceId;

    public function __construct(string $type, string $serviceId = null)
    {
        $this->type = $type;
        $this->serviceId = $serviceId;
    }
}
