<?php

namespace Primavera\Doctrine\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Primavera\Data\TypeAwareObjectHydrator;

class CollectionMapper implements TypeAwareObjectHydrator
{
    public function hydrate($object, $data, array &$context = null): array | object
    {
        return new ArrayCollection($data);
    }

    public function getSupportedClassName(): string
    {
        return Collection::class;
    }
}