<?php

namespace Primavera\PersistenceTests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Tools\DsnParser;
use PHPUnit\Framework\TestCase;

class DbTestCase extends TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    protected function setUp(): void
    {
        @unlink('test_database.db');
        // $this->connection = DriverManager::getConnection(['url' => 'sqlite3::test_database.db']);
        $this->connection = DriverManager::getConnection((new DsnParser())->parse('sqlite3::test_database.db'));

        $schema = new Schema();
        $userTable = $schema->createTable('users');
        $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $userTable->addColumn('name', 'string', ['length' => 256]);
        $userTable->addColumn('email', 'string', ['length' => 256]);
        $userTable->addColumn('type', 'string', ['length' => 256]);
        $userTable->setPrimaryKey(['id']);

        $this->connection->executeQuery(implode(';', $schema->toSql($this->connection->getDatabasePlatform())));
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        $this->connection = null;
        unlink('test_database.db');
    }
}