<?php

namespace Primavera\Persistence\Persister;

use Primavera\Persistence\Database\TableInterface;

interface PersisterInterface extends TableInterface
{
    public function save($data);
}