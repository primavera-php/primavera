<?php

namespace Primavera\Persistence\Parser;

interface WalkerInterface
{
    public function on($type, callable $handler);

    public function walk();
}