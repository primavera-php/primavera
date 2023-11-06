<?php

namespace Primavera\Persistence\Parser;

class AnnotationWalker implements WalkerInterface
{
    protected array $handlers;

    protected $metadata;

    public static function create($metadata): self
    {
        $walker = new self();
        $walker->metadata = $metadata;

        return $walker;
    }

    public function on($type, callable $handler)
    {
        $this->handlers[$type] = $handler;
    }

    public function walk()
    {
        foreach ($this->handlers as $type => $handler) {
            if ($this->metadata->hasAnnotation($type)) {
                $handler($this->metadata->getAnnotation($type), $this->metadata);
            }
        }
    }
}