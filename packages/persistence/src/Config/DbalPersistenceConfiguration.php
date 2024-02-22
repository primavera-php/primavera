<?php

namespace Primavera\Persistence\Config;

use Doctrine\DBAL\Connection;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\Bean\BeanRegisterer;
use Primavera\Persistence\BeanProcessor\DbalPersisterImplementor;
use Primavera\Persistence\BeanProcessor\DbalRepositoryImplementor;
use Primavera\Persistence\Parser\DbalExpressionFactory;
use Primavera\Container\Annotation\Bean;
use Primavera\Persistence\Parser\ParserInterface;
use Primavera\Persistence\Parser\SimpleSqlParser;
use Primavera\Persistence\Stereotype\Persister;
use Primavera\Persistence\Stereotype\Repository;

class DbalPersistenceConfiguration
{
    #[Bean]
    public function dbalExpressionFactory(Connection $connection): DbalExpressionFactory
    {
        return new DbalExpressionFactory($connection, new SimpleSqlParser($connection));
    }

    #[Bean]
    public function dbalRepositoryImplementor(ParserInterface $parser): DbalRepositoryImplementor
    {
        return new DbalRepositoryImplementor($parser);
    }

    #[Bean]
    public function dbalPersisterImplementor(ParserInterface $parser): DbalPersisterImplementor
    {
        return new DbalPersisterImplementor($parser);
    }

    #[Configurator]
    public static function addStereotypes(BeanRegisterer $beanRegisterer)
    {
        $beanRegisterer->addStereotype(Repository::class)
            ->addStereotype(Persister::class);
    }
}