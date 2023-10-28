<?php

namespace Vox\Persistence\Stereotype;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({'CLASS'})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Repository
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
