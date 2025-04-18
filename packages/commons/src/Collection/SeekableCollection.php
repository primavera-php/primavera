<?php

namespace Primavera\Commons\Collection;
use ReturnTypeWillChange;

/**
 * @template T
 * 
 * @extends CollectionInterface<T>
 */
interface SeekableCollection extends CollectionInterface
{
    /**
     * @param callable(T) $filter
     * 
     * @return T
     */
    public function seek(callable $filter) : mixed;
}
