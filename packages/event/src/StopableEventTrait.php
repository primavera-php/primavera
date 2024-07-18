<?php

namespace Primavera\Event;

trait StopableEventTrait
{
    private bool $stoped = false;

    public function stopPropagation() 
    {
        $this->stoped = true;
    }

    public function isPropagationStopped(): bool 
    {
        return $this->stoped;
    }
}
