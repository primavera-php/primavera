<?php

namespace Primavera\Persistence\Repository;

use Primavera\Persistence\Database\TableInterface;

/**
 * @template T
 * @template I
 */
interface RepositoryInterface extends TableInterface
{
    /**
     * @return \Traversable<T>
     */
    public function find(...$criteria): \Traversable;

    /**
     * @param I $id
     * 
     * @return T
     */
    public function findById($id);

    /**
     * @return T
     */
    public function findOne(...$criteria);
}