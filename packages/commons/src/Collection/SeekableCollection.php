<?php

namespace Primavera\Commons\Collection;
use ReturnTypeWillChange;

/**
 * @template T
 */
interface SeekableCollection
{
    /**
     * @param callable(T) $filter
     * 
     * @return T
     */
    #[ReturnTypeWillChange]
    public function seek(callable $filter) : mixed;
}
