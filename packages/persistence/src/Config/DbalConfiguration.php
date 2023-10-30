<?php
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PhpBeans\Annotation\Injects;

class DbalConfiguration
{
    public function connection(#[Injects("database.connectionString")] string $connectionString): Connection
    {
        return DriverManager::getConnection(['url' => $connectionString]);
    }
}