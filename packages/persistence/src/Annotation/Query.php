<?php

namespace Primavera\Persistence\Annotation;

/**
 * @Annotation
 * @Target({'METHOD'})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Query
{
    /**
     * @var string
     * @Required
     */
    public $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }
}
