<?php

namespace Primavera\Doctrine\Test\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Primavera\Data\Mapping\Exclude;

#[Entity]
#[Table('phones')]
class Phone
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id = null;

    public function __construct(
        #[ManyToOne(cascade: ['persist'], inversedBy: 'users')]
        #[Exclude]
        public User $user,
        
        #[Column]
        public string $phone,
    ) {}
}
