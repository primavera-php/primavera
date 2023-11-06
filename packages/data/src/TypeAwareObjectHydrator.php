<?php

namespace Primavera\Data;

interface TypeAwareObjectHydrator extends ObjectHydratorInterface
{
    public function getSupportedClassName(): string;
}