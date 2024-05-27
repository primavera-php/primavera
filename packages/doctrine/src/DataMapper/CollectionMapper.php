<?php

namespace Primavera\Doctrine\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use LogicException;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Data\ObjectHydratorInterface;
use Primavera\Data\PropertyAccessor;
use Primavera\Data\TypeAwareObjectHydrator;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\PropertyMetadata;

#[IgnoreScanner]
class CollectionMapper implements TypeAwareObjectHydrator
{
    private PropertyAccessor $propertyAcessor;

    public function __construct(
        private MetadataFactoryInterface $metadataFactory,
    ) {
        $this->propertyAcessor = new PropertyAccessor($metadataFactory);
    }

    public function hydrate($object, $data, array &$context = null): array | object
    {
        $property = $context['property'];

        foreach ($data as &$item) {
            if (!is_object($item)) {
                $item = $this->hydrateEntity($context['object'] ?? null, $item, $context['hydrator'], $property);
            }
        }

        return new ArrayCollection($data);
    }

    private function hydrateEntity(object $object, $data, ObjectHydratorInterface $hydrator, PropertyMetadata $propertyMetadata)
    {
        $mapping = null;

        if ($propertyMetadata->hasAnnotation(OneToMany::class)) {
            $mapping = $propertyMetadata->getAnnotation(OneToMany::class);
        } elseif ($propertyMetadata->hasAnnotation(ManyToMany::class)) {
            $mapping = $propertyMetadata->getAnnotation(ManyToMany::class);
        }

        $entityName = $mapping?->targetEntity;
        $mappedBy = $mapping?->mappedBy;

        if (!$entityName) {
            throw new LogicException("since type mapping and doctrine relationship mapping lacks the type, we cannot hydrate this property");
        }

        $entity = $hydrator->hydrate($object?->{$mappedBy} ?? $entityName, $data);

        if ($mappedBy) {
            $this->propertyAcessor->set($entity, $mappedBy, $object);
        }

        return $entity;
    }

    public function getSupportedClassName(): string
    {
        return Collection::class;
    }
}