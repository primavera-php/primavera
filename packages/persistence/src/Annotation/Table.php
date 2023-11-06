<?php

namespace Primavera\Persistence\Annotation;

/**
 * @Annotation
 * @Target({'CLASS'})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Table
{
    /**
     * @var string
     * @Required
     */
    public $tableName;

    /**
     * @var string
     * @Required
     */
    public $idColunmName;

    public function __construct(string $tableName, string $idColumnName = 'id')
    {
        $this->tableName = $tableName;
        $this->idColunmName = $idColumnName;
    }
}
