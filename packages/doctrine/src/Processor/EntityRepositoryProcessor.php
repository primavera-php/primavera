<?php

namespace Primavera\Doctrine\Processor;

use Primavera\Container\Processor\AbstractStereotypeProcessor;
use Primavera\Doctrine\Stereotype\Repository;

class EntityRepositoryProcessor extends AbstractStereotypeProcessor
{
    public function getStereotypeName(): string
    {
        return Repository::class;
    }

    public function process($repository)
    {
        $metadata = $this->getContainer()->getMetadata($repository);

        $repoAnnotation = $metadata->getAnnotation(Repository::class);
    }
}