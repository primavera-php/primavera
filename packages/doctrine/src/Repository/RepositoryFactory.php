<?php

declare(strict_types=1);

namespace Primavera\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Container\Factory\StereotypeFactoryInterface;
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
    ) {}

    public function addRegisteredRepository(EntityRepository $entityRepository)
    {
        $this->registeredRepositories[$entityRepository->getClassName()] = $entityRepository;
    }

    public function getRepository(EntityManagerInterface $entityManager, string $entityName): EntityRepository
    {
        return $this->registeredRepositories[$entityName] ??= $this->defaultRepositoryFactory->getRepository($entityManager, $entityName);
    }

    public function create(ContainerInterface $container, ClassMetadataInterface $metadata, array $params): EntityRepository
    {
        if ($metadata->getName() === EntityRepository::class) {

        }
    }
}
