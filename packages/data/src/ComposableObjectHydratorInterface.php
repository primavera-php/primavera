<?php

namespace Primavera\Data;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface ComposableObjectHydratorInterface extends ObjectHydratorInterface
{
    public function addHydrator(TypeAwareObjectHydrator $hydrator);
}
