<?php

namespace Primavera\Commons\Collection;

use IteratorAggregate;
use ReturnTypeWillChange;

/**
 * @template T
 * @template I
 */
interface CollectionInterface extends IteratorAggregate
{
    /**
     * @param T $item
     * @param I $index
     */
    public function add($item, $index = null): CollectionInterface;

    /**
     * @param I $index
     */
    public function has($index): bool;

    /**
     * @param I $index
     * @return T
     */
    #[ReturnTypeWillChange]
    public function get($index): mixed;
}
