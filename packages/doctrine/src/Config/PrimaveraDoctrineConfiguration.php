<?php

namespace Primavera\Doctrine\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Annotation\Injects;
use Primavera\Container\Bean\BeanRegisterer;
use Primavera\Container\ConfigurationData;
use Primavera\Doctrine\Container\RepositoryInjector;
use Primavera\Doctrine\DataMapper\CollectionMapper;
use Primavera\Doctrine\Repository\RepositoryFactory;

class PrimaveraDoctrineConfiguration
{
    #[Configurator]
    public static function configureDoctrineModule(BeanRegisterer $beanRegisterer)
    {
        $beanRegisterer->addStereotype(EntityRepository::class)
            ->addComponent(DefaultRepositoryFactory::class)
            ->addComponent(RepositoryFactory::class)
            ->addComponent(RepositoryInjector::class)
            ->addComponent(CollectionMapper::class);
    }

    #[Bean]
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