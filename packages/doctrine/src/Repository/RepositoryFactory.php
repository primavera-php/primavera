<?php

namespace Primavera\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\InvalidEntityRepository;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Container\Factory\StereotypeFactoryInterface;
use Primavera\Doctrine\Container\RepositoryInjector;
use Primavera\Metadata\ClassMetadataInterface;
use Psr\Container\ContainerInterface;

/**
 * @extends StereotypeFactoryInterface<EntityRepository>
 */
#[IgnoreScanner]
class RepositoryFactory implements RepositoryFactoryInterface, StereotypeFactoryInterface
{
    /**
     * @var EntityRepository[]
     */
    private array $registeredRepositories = [];

    public function __construct(
        private DefaultRepositoryFactory $defaultRepositoryFactory,
        private RepositoryInjector $repositoryInjector,
    ) {}

    protected function addRegisteredRepository(EntityRepository $entityRepository)
    {
        return $this->registeredRepositories[$entityRepository->getClassName()] = $entityRepository;
    }

    public function getRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        return $this->registeredRepositories[$entityName] ??= $this->defaultRepositoryFactory->getRepository($entityManager, $entityName);
    }

    /**
     * @param ContainerInterface $container
     * @param ClassMetadataInterface $metadata
     * @param \Primavera\Metadata\ParamMetadata[] $params
     */
    public function create(ContainerInterface $container, ClassMetadataInterface $metadata, array $params): EntityRepository
    {
        if (!$metadata->instanceOf(EntityRepository::class)) {
            throw new \InvalidArgumentException('This factory can only create EntityRepository instances');
        }

        if (!$metadata->hasGenerics()) {
            throw new InvalidEntityRepository("An repository should have the generics information eg: @extends EntityRepository<User> in order to be instantiated by this module");
        }

        $em = $container->get(EntityManagerInterface::class);
        $entityName = $metadata->getGenericsInfo()['decoration'];
        $doctrineMetadata = $em->getClassMetadata($entityName);

        $params = [
            ...[$em, $doctrineMetadata],
            ...array_map(
                function ($p) use ($container) {
                    if ($this->repositoryInjector->canIntercept($p)) {
                        return $this->repositoryInjector->resolve($p, $container);
                    }

                    return $container->get($p->getId());
                },
                array_slice($params, 2)),
        ];

        return $this->addRegisteredRepository($metadata->getReflection()->newInstanceArgs($params));
    }
}
