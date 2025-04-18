<?php

namespace Primavera\Commons\Collection;

trait ToArrayTrait
{
    public function toArray(): array
    {
        return iterator_to_array($this);
    }
}
