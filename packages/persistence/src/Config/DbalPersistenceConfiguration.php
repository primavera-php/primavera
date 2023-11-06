<?php

namespace Primavera\Persistence\Config;

use Doctrine\DBAL\Connection;
use Primavera\Persistence\BeanProcessor\DbalRepositoryImplementor;
use Primavera\Persistence\Parser\DbalExpressionFactory;
use Primavera\Container\Annotation\Bean;
use Primavera\Persistence\Parser\ParserInterface;
use Primavera\Persistence\Parser\SimpleSqlParser;

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
}