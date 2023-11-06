<?php

namespace Primavera\Persistence\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class GroupBy
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
