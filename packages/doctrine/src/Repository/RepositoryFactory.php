<?php

namespace Primavera\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Primavera\Container\Annotation\Autowired;
use Primavera\Container\ContainerAwareInterface;
use Primavera\Container\ContainerAwareTrait;
use Primavera\Doctrine\Container\RepositoryPreProcessor;
use Primavera\Metadata\Factory\MetadataFactory;
use Primavera\Metadata\ParamMetadataInterface;

class RepositoryFactory implements RepositoryFactoryInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var EntityRepository[]
     */
    private array $registeredRepositories = [];

    private array $repositoryMap = [];

    public function __construct(
        private DefaultRepositoryFactory $defaultRepositoryFactory,
        private MetadataFactory $mf,
    ) {}

    public function getRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        if ($mappedRepo = RepositoryPreProcessor::getMap($entityName)) {
            return $this->registeredRepositories[$entityName] ??= $this->resolveRepository($mappedRepo, $entityName, $entityManager);
        }

        return $this->registeredRepositories[$entityName] ??= $this->defaultRepositoryFactory->getRepository($entityManager, $entityName);
    }

    protected function resolveRepository(string $mappedRepo, string $entityName, EntityManagerInterface $entityManager)
    {
        $metadata = $this->mf->getMetadataForClass($mappedRepo);

        $getParamType = fn(ParamMetadataInterface $paramMetadata) 
            => $paramMetadata->getType() 
                ? $this->mf->getMetadataForClass($paramMetadata->getType()) 
                : null;

        $params = [];

        foreach ($metadata->getMethodMetadata('__construct')->getParams() as $paramMetadata) {
            if ($getParamType($paramMetadata)?->instanceOf(ClassMetadata::class)) {
                $params[] = $entityManager->getClassMetadata($entityName);
            } elseif ($paramType = $paramMetadata->getType()) {
                $params[] = $this->container->get($paramType, $paramMetadata);
            } else {
                $params[] = $this->container->get($paramMetadata->getName(), $paramMetadata);
            }
        }

        $instance = $metadata->getReflection()->newInstanceArgs($params);

        foreach ($metadata->getAnnotatedPropertiesMetadata(Autowired::class) as $autowired) {
            $id = $autowired->getAnnotation(Autowired::class)->id ?? $autowired->getType() ?? $autowired->getName();
            $autowired->setValue($instance, $this->container->get($id));
        }

        return $instance;
    }
}

