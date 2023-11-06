<?php

namespace Primavera\Persistence\Persister;

use Doctrine\DBAL\Connection;

abstract class DbalBasePersister implements PersisterInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save($data)
    {
        // TODO: Implement save() method.
    }
}