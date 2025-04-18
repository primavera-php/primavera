<?php

namespace Primavera\Doctrine\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Primavera\Container\Annotation\Configuration;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\ConfigurationData;
use Primavera\Container\ContainerBuilder;
use Primavera\Doctrine\Container\RepositoryInjector;
use Primavera\Doctrine\Container\RepositoryPreProcessor;
use Primavera\Doctrine\Repository\RepositoryFactory;

#[Configuration]
class DoctrineConfiguration
{
    #[Configurator]
    public static function configureDoctrineModule(ContainerBuilder $builder)
    {
        $builder->withPreProcessors(new RepositoryPreProcessor())
            ->withComponents(
                DefaultRepositoryFactory::class,
                RepositoryFactory::class,
                RepositoryInjector::class,
                // CollectionMapper::class,
                // DoctrineMergeResolver::class,
                // DoctrineMetadataResolver::class,
            );
    }

    #[Factory]
    public function entityManager(ConfigurationData $configurationData, RepositoryFactory $repositoryFactory, #[Injects('debug')] $debug = false): EntityManager
    {
        $config = match ($configurationData->doctrine->metadataDriver ?? 'annotation') {
            'annotation' => ORMSetup::createAttributeMetadataConfiguration(
                $configurationData->doctrine->entityPaths,
                $debug,
                $configurationData->doctrine->proxyDir,
            ),
            'xml' => ORMSetup::createXMLMetadataConfiguration(
                $configurationData->doctrine->xmlPaths,
                $debug,
                $configurationData->doctrine->proxyDir,
            )
        };

        $config->setRepositoryFactory($repositoryFactory);

        $connection = DriverManager::getConnection((new DsnParser())->parse($configurationData->doctrine->connectionString));

        return new EntityManager($connection, $config);
    }

}
