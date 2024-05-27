<?php

namespace Primavera\Data;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface PropertyAccessorInterface
{
    public function get($object, string $name);
    
    public function set($object, string $name, $value);

    public function tryGet(object $object, string $name, $defaultValue = null);
}
