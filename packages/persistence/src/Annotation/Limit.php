<?php

namespace Primavera\Persistence\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Limit
{
    /**
     * @var string
     * @Required
     */
    public $limit;

    public function __construct(string $limit)
    {
        $this->limit = $limit;
    }
}
