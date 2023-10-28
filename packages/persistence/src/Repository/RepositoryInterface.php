<?php

namespace Vox\Persistence\Repository;

use Vox\Persistence\Database\TableInterface;

interface RepositoryInterface extends TableInterface
{
    public function find(...$criteria): \Traversable;

    public function findById($id);

    public function findOne(...$criteria);
}