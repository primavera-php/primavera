<?php


namespace Primavera\Framework\Collection;


class CallbackPriorityQueue extends \SplHeap
{
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
}