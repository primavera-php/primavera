<?php

namespace Primavera\Container;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class FlattenArrayIterator extends RecursiveIteratorIterator
{
    public function __construct(array $data)
    {
        parent::__construct(
            new class($data) extends RecursiveArrayIterator
            {
                private $parent = null;

                public function __construct(array $data, $parent = null)
                {
                    parent::__construct($data);

                    $this->parent = $parent;
                }

                public function hasChildren() : bool
                {
                    return is_array($this->current());
                }

                public function getChildren() : RecursiveArrayIterator
                {
                    return new self($this->current(), $this->key());
                }
				
				public function key(): string|int|null
				{
					if ($this->parent) {
						return "{$this->parent}." . parent::key();
					}
					
					return parent::key();
				}
            },
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
