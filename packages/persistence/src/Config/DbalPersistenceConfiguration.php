<?php

namespace Primavera\Persistence\Config;

use Doctrine\DBAL\Connection;
use Primavera\Container\Annotation\Configurator;
use Primavera\Container\ContainerBuilderInterface;
use Primavera\Persistence\Parser\DbalExpressionFactory;
use Primavera\Container\Annotation\Factory;
use Primavera\Persistence\Parser\MethodNameToQueryParser;
use Primavera\Persistence\Parser\SimpleSqlParser;
use Primavera\Persistence\Processor\DbalPersisterImplementor;
use Primavera\Persistence\Processor\DbalRepositoryImplementor;

class DbalPersistenceConfiguration
{
    #[Configurator]
    public static function configure(ContainerBuilderInterface $cb)
    {
        $cb->withComponents(
            MethodNameToQueryParser::class,
            DbalRepositoryImplementor::class,
            DbalPersisterImplementor::class,
        );
    }

    #[Factory]
    public function dbalExpressionFactory(Connection $connection): DbalExpressionFactory
    {
        return new DbalExpressionFactory($connection, new SimpleSqlParser($connection));
    }
}