<?php

namespace Primavera\Commons\Collection;
use ReturnTypeWillChange;

/**
 * @template T
 */
interface FilterableCollection
{
    /**
     * @param callable(T) $filter
     * 
     * @return T
     */
    #[ReturnTypeWillChange]
    public function filter(callable $filter) : FilterableCollection;
}
