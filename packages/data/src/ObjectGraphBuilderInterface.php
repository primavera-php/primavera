<?php

namespace Primavera\Data;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface ObjectGraphBuilderInterface
{
    public function buildObjectGraph($object);
    
    public function clear();
}
