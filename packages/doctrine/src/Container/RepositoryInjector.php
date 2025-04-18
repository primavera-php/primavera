<?php

namespace Primavera\Doctrine\Container;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Primavera\Container\Annotation\EventListener;
use Primavera\Container\Event\BeforeGetComponent;
use Primavera\Container\Event\BeforeInstanceComponentEvent;
use Primavera\Container\Exception\ContainerException;
use Primavera\Doctrine\Annotation\InjectRepository;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\ParamMetadata;
use Psr\Container\ContainerInterface;

#[EventListener]
class RepositoryInjector
{
    public function __construct(
        private MetadataFactoryInterface $mf,
    ) {}

    public function __invoke(BeforeGetComponent $e)
    {
        if ($this->canIntercept($e)) {
            $e->result = $this->resolve($e->container, $e->component, $e->paramMetadata);
        }
    }

    public function canIntercept(BeforeGetComponent $e): bool
    {
        return (bool) $e->paramMetadata?->hasAnnotation(InjectRepository::class);
    }

    public function resolve(ContainerInterface $container, string $component, ?ParamMetadata $paramMetadata): EntityRepository
    {
        $entityClassName = $paramMetadata->getAnnotation(InjectRepository::class)?->entityClassName;

        return $container->get(EntityManagerInterface::class)->getRepository($entityClassName)
            ?? throw new ContainerException("Repository for Entity {$entityClassName} not found for injection on {$paramMetadata->getClass()}");
    }
}
