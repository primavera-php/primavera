<?php

namespace Vox\Persistence\Parser;

interface WalkerInterface
{
    public function on($type, callable $handler);

    public function walk();
}