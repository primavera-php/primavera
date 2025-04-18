<?php


namespace Primavera\Commons\Collection;

class CallbackPriorityQueue extends \SplHeap implements CollectionInterface
{
    use ToArrayTrait;
    
    /**
     * @var callable(object, object): int
     */
    private $comparator;

    public function __construct(callable $comparator, iterable $collection = null)
    {
        $this->comparator = $comparator;

        if ($collection) {
            foreach ($collection as $item) {
                $this->insert($item);
            }
        }
    }

    protected function compare($value1, $value2)
    {
        return call_user_func($this->comparator, $value1, $value2);
    }

    public function add($item, $index = null): CollectionInterface
    {
        if ($index) {
            trigger_error("a priority queue is not indexable", E_USER_WARNING);
        }

        $this->insert($item);

        return $this;
    }
    
    public function get($index): mixed
    {
        throw new \RuntimeException("a priority queue is not seekable");
    }
    
    public function has($index): bool
    {
        throw new \RuntimeException("a priority queue is not seekable");
    }
}