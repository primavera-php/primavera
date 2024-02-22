<?php

namespace Primavera\Persistence\Persister;

use Primavera\Persistence\Database\TableInterface;

/**
 * @template T
 * @template I
 */
interface PersisterInterface extends TableInterface
{
    /**
     * @param T $data
     * 
     * @return T
     */
    public function save($data);

    /**
     * @return T
     */
    public function insert(array $data);

    /**
     * @return T
     */
    public function update(array $data);

    /**
     * @param I $id
     */
    public function delete($id);
}