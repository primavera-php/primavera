<?php

namespace Vox\Persistence\Config;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PhpBeans\Annotation\Bean;
use PhpBeans\Annotation\Injects;

class DbalConfiguration
{
    #[Bean]
    public function connection(#[Injects("database.connectionString")] string $connectionString): Connection
    {
        return DriverManager::getConnection(['url' => $connectionString]);
    }
}