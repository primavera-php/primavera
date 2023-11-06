<?php

namespace Primavera\Container\Event;

use Psr\EventDispatcher\StoppableEventInterface;

class BeanEvent implements StoppableEventInterface
{
    protected $bean;
    
    protected string $name;
    
    protected bool $stoped = false;
    
    public function __construct($bean, string $name) {
        $this->bean = $bean;
        $this->name = $name;
    }
    
    public function getBean() {
        return $this->bean;
    }

    public function getName(): string {
        return $this->name;
    }
    
    public function isPropagationStopped(): bool {
        return $this->stoped;
    }

    public function stopPropagation() {
        $this->stoped = true;
    }
}
