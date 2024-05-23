<?php

namespace Primavera\Doctrine\Test\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table('users')]
class User
{
    #[Id]
    #[Column]
    #[GeneratedValue]
    public ?int $id = null;

    /**
     * @param $phones ArrayCollection<Phone>
     */
    public function __construct(
        #[Column]
        public string $name,

        #[Column]
        public string $email,

        #[Column]
        public string $type,
        
        #[OneToMany(Phone::class, mappedBy: 'user', cascade: ['all'])]
        private Collection $phones = new ArrayCollection(),
    ) {}


    public function getPhones(): Collection
    {
        return $this->phones ??= new ArrayCollection();
    }
}
