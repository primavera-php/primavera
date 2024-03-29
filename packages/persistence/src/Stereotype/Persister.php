<?php

namespace Primavera\Persistence\Stereotype;

use Doctrine\Common\Annotations\Annotation\Required;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Persister
{
    /**
     * @var string
     * @Required
     */
    public $entity;

    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }
}
