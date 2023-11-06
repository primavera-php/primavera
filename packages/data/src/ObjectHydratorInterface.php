<?php

namespace Primavera\Data;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface ObjectHydratorInterface
{
    public function hydrate($object, array $data): object;
}
