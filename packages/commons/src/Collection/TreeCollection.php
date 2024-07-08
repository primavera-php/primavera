<?php

namespace Primavera\Commons\Collection;

use RecursiveIterator;

class TreeCollection implements RecursiveIterator
{
    private array $vertices = [];

    private array $children = [];

    private array $parents = [];

    private array $paths = [];

    private array $roots = [];

    private array $leaves = [];

    public function addVertex($parent, $child)
    {
        $this->vertices[] = [$parent => $child];
        $this->children[$parent] ??= [];
        $this->children[$parent][] = $child;

        $this->parents[$child] ??= [];
        $this->parents[$child][] = $parent;

        $found = false;
        $newPaths = [];

        foreach ($this->paths as $idx => &$path) {
            $original = $path;
            $start = $path[0];
            $end = end($path);

            if ($end === $parent) {
                $found = true;
                $path[] = $child;
            }

            if ($start === $child) {
                $found = true;
                array_unshift($path, $parent);
            }

            if ($end !== $parent 
                && $pos = array_search($parent, $original)
                && $original[$pos + 1] !== $child) {
                $found = true;
                $newPath = array_slice($original, 0, $pos + 1);
                $newPath[] = $child;
                $newPaths[] = $newPath;
            }

            $this->roots[$idx] = $path[0];
            $this->leaves[$idx] = end($path);
        }

        foreach ($newPaths as $newPath)
            $this->addPath($newPath);

        if (!$found)
            $this->addPath([$parent, $child]);
    }

    private function addPath($path)
    {
        $this->paths[] = $path;
        $idx = count($this->paths) - 1;
        $this->roots[$idx] = $path[0];
        $this->leaves[$idx] = end($path);
    }

    public function getChildren()
    {
        return clone $this;
    }

	public function hasChildren()
    {
        count($this->children[$this->current()] ?? []) > 0;
    }

    function current(): mixed
    {
        return current($this->roots);
    }

	function key(): mixed
    {
        return key($this->roots);
    }

	function next(): void
    {
        next($this->roots);
    }

	function rewind(): void
    {
        reset($this->roots);
    }

	function valid(): bool
    {
        return $this->key() !== null;
    }

    public function getRoots()
    {
        return $this->roots;
    }

    public function getLeaves()
    {
        return $this->leaves;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    private function __clone()
    {
        $this->roots = $this->children[$this->current()];
        $this->rewind();
    }
}
