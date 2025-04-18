<?php

namespace Primavera\Commons\Collection;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 * 
 * @extends CollectionInterface<T, scalar>
 */
class Collection implements CollectionInterface, IteratorAggregate
{
    use ToArrayTrait;

    public function __construct(
        private array $data = [],
    ) {}

    public function add($item, $index = null): CollectionInterface
    {
        $index ? $this->data[$index] = $item : $this->data[] = $item;

        return $this;
    }

    public function has($index): bool
    {
        return isset($this->data[$index]);
    }

    public function get($index): mixed
    {
        return $this->data[$index] ?? null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
