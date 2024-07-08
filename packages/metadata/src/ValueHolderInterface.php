<?php

namespace Primavera\Metadata;

interface ValueHolderInterface
{
    function getValue(?object $object);

    function  setValue(?object $object, $value): void;
}
