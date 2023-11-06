<?php

namespace Primavera\Persistence\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class OrderBy
{
    /**
     * @var array
     * @Required
     */
    public array $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }
}
