<?php

namespace Primavera\Commons\Collection;

/**
 * @template T
 * 
 * @extends CollectionInterface<T>
 */
interface FilterableCollection extends CollectionInterface
{
    /**
     * @param callable(T) $filter
     * 
     * @return FilterableCollection<T>
     */
    public function filter(callable $filter) : FilterableCollection;
}
