<?php

namespace Primavera\Doctrine\Container;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Primavera\Container\ContainerBuilderInterface;
use Primavera\Container\Processor\ComponentPreProcessorInterface;
use Primavera\Doctrine\Stereotype\DoctrineRepository;
use Primavera\Metadata\ClassMetadataInterface;

class RepositoryPreProcessor implements ComponentPreProcessorInterface
{
    private static $repoMap = [];

    public function process(ClassMetadataInterface $component, ContainerBuilderInterface $containerBuilder) 
    {
        $dr = $component->getAnnotation(DoctrineRepository::class);

        if (!$dr && $ext = $component->getGenericsInfo()['decoration'] ?? null) {
            $entityName = $ext;
        } elseif($dr) {
            $entityName = $dr->entityName;
        } else {
            return;
        }

        self::$repoMap[$entityName] = $component->getName();

        // $containerBuilder->withComponents($component->getName());
    }
    
    public function canProcess(object $component): bool 
    {
        return $component->hasAnnotation(DoctrineRepository::class)
            || $component->instanceOf(EntityRepository::class);
    }

    public static function getMap(string $entityName): ?string
    {
        return self::$repoMap[$entityName] ?? null;
    }
}
