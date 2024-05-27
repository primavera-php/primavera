<?php

namespace Primavera\Doctrine\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\PersistentCollection;
use LogicException;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Data\Mapping\Bindings;
use Primavera\Data\ObjectHydratorInterface;
use Primavera\Data\PropertyAccessor;
use Primavera\Data\PropertyAccessorInterface;
use Primavera\Data\TypeAwareObjectHydrator;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\PropertyMetadata;
use RuntimeException;

#[IgnoreScanner]
class CollectionMapper implements TypeAwareObjectHydrator
{
    private PropertyAccessorInterface $propertyAcessor;

    private array $metadatas = [];

    public function __construct(
        private MetadataFactoryInterface $metadataFactory,
        private EntityManagerInterface $em,
    ) {
        $this->propertyAcessor = new PropertyAccessor($metadataFactory);
    }

    public function hydrate($collection, $data, array &$context = null): array | object
    {
        $property = $context['property'];

        foreach ($data as $index => &$item) {
            if (!is_object($item)) {
                $item = $this->hydrateEntity($context['object'], $item, $context['hydrator'], $property, $index);
            }
        }

        return $collection instanceof PersistentCollection ? $collection : new ArrayCollection($data);
    }

    private function hydrateEntity(object $mainEntity, $data, ObjectHydratorInterface $hydrator, PropertyMetadata $propertyMetadata, $index)
    {
        $mapping = null;

        if ($propertyMetadata->hasAnnotation(OneToMany::class)) {
            $mapping = $propertyMetadata->getAnnotation(OneToMany::class);
        } elseif ($propertyMetadata->hasAnnotation(ManyToMany::class)) {
            $mapping = $propertyMetadata->getAnnotation(ManyToMany::class);
        }

        $targetEntity = $mapping?->targetEntity;
        $mappedBy = $mapping?->mappedBy;

        if (!$targetEntity) {
            throw new LogicException("since type mapping and doctrine relationship mapping lacks the type, we cannot hydrate this property");
        }

        $entity = $hydrator->hydrate(
            $this->getEntity($mainEntity, $targetEntity, $data, $propertyMetadata, $index),
            $data
        );

        if ($mappedBy) {
            $this->propertyAcessor->set($entity, $mappedBy, $mainEntity);
        }

        return $entity;
    }

    public function getEntity(object $mainentity, string $targetEntity, array $data, PropertyMetadata $propertyMetadata, $index): object | string
    {
        return $this->getEntityById($mainentity, $propertyMetadata, $targetEntity, $data) 
            ?? $this->getEntityByIndex($mainentity, $propertyMetadata, $index)
            ?? $targetEntity;
    }

    public function getEntityByIndex(object $mainEntity, PropertyMetadata $propertyMetadata, $index)
    {
        return $this->propertyAcessor->tryGet($mainEntity, $propertyMetadata->getName(), [])[$index] ?? null;
    }

    public function getEntityById(object $mainEntity, PropertyMetadata $propertyMetadata, string $targetEntity, $data)
    {
        $collection = $this->propertyAcessor->tryGet($mainEntity, $propertyMetadata->getName());
        $entityMetadata = $this->em->getClassMetadata($targetEntity);
        $objectMetadata = $this->metadataFactory->getMetadataForClass($targetEntity);
        $idFields = $entityMetadata->getIdentifierFieldNames();

        $criteria = Criteria::create();

        foreach ($data as $f => $v) {
            $propertyMetadata = $this->getPropertyMetadata($objectMetadata, $f);
            
            if (!in_array($propertyMetadata->getName(), $idFields)) {
                continue;
            }

            $criteria->andWhere(Criteria::expr()->eq($propertyMetadata->getName(), $v));
        }


        return $collection?->matching($criteria)?->first() ?: null;
    }

    private function getPropertyMetadata(ClassMetadataInterface $metadata, string $name): PropertyMetadata
    {
        $getPropertyMetadata = function () use ($metadata, $name) {
            foreach ($metadata->getPropertyMetadata() as $property) {
                if (
                    (
                        $property->hasAnnotation(Bindings::class)
                        && $property->getAnnotation(Bindings::class)->source === $name
                    ) || $property->getName() === $name
                ) {
                    return $property;
                }
            }

            throw new RuntimeException("property $name doesn't exists on {$metadata->getName()}");
        };

        return $this->metadatas[$metadata->getName()][$name] ??= $getPropertyMetadata();
    }


    public function getSupportedClassName(): string
    {
        return Collection::class;
    }
}