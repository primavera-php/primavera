<?php

function iterator_map(iterable $iterator, callable $callable): array
{
    $data = [];

    foreach ($iterator as $item) {
        $value = $callable($item);

        $data[] = $value;
    }

    return $data;
}