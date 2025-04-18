<?php

namespace Primavera\Commons\Collection;

use Traversable;

/**
 * @template T
 * @template I
 */
interface CollectionInterface extends Traversable
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
    public function get($index): mixed;

    public function toArray(): array;
}
