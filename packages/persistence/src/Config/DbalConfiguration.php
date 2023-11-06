<?php

namespace Primavera\Persistence\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Injects;

class DbalConfiguration
{
    #[Bean]
    public function connection(#[Injects("database.connectionString")] string $connectionString): Connection
    {
        return DriverManager::getConnection(['url' => $connectionString]);
    }
}