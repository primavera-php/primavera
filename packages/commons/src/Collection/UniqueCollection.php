<?php

namespace Primavera\Commons\Collection;
use ArrayIterator;
use Traversable;

/**
 * @template T
 * @template I
 * 
 * @extends CollectionInterface<T, I>
 */
class UniqueCollection implements CollectionInterface
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = array_unique($data);
    }

    public function get($index): mixed
    {
        return $this->data[$index];
    }

    /**
     * @param T $item
     */
    public function has($item): bool
    {
        return in_array($item, $this->data, true);
    }

    public function add($item, $index = null): CollectionInterface
    {
        if (!$this->has($item)) {
            $index ? $this->data[$index] = $item : $this->data[] = $item;
        }

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
