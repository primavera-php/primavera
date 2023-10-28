<?php

namespace Vox\Persistence\Config;

use Doctrine\DBAL\Connection;
use Vox\Persistence\BeanProcessor\DbalRepositoryImplementor;
use Vox\Persistence\Parser\DbalExpressionFactory;
use PhpBeans\Annotation\Bean;
use Vox\Persistence\Parser\ExpressionFactoryInterface;
use Vox\Persistence\Parser\ParserInterface;
use Vox\Persistence\Parser\SimpleSqlParser;

class DbalPersistenceConfiguration
{
    /**
     * @Bean
     */
    #[Bean]
    public function dbalExpressionFactory(Connection $connection): ExpressionFactoryInterface
    {
        return new DbalExpressionFactory($connection, new SimpleSqlParser($connection));
    }

    #[Bean]
    public function dbalRepositoryImplementor(ParserInterface $parser): DbalRepositoryImplementor
    {
        return new DbalRepositoryImplementor($parser);
    }
}