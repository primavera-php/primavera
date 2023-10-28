<?php

namespace Vox\Persistence\Persister;

use Vox\Persistence\Database\TableInterface;

interface PersisterInterface extends TableInterface
{
    public function save($data);
}