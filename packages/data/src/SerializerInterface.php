<?php

namespace Primavera\Data;

interface SerializerInterface
{
    public function registerFormat($formatter, string $format = null);

    public function serialize(string $format, $data, array &$context = []);

    public function deserialize(string $format, $object, $data, array &$context = []);
}