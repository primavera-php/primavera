<?php


namespace Primavera\Metadata\Test\Stub;

use Attribute;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class TestAnnotation
{
    /**
     * @var string
     */
    public string $name = 'default';

    public ?string $test = null;

    public function __construct($name = 'default')
    {
        $this->name = $name;
    }
}