<?php

namespace Primavera\Persistence\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Primavera\Container\Annotation\Factory;
use Primavera\Container\Annotation\Injects;

class DbalConfiguration
{
    #[Factory]
    public function connection(#[Injects("database.connectionString")] string $connectionString): Connection
    {
        // return DriverManager::getConnection(['url' => $connectionString]);
        return DriverManager::getConnection((new DsnParser())->parse($connectionString));
    }
}