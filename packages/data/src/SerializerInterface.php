<?php

namespace Primavera\Data;

interface SerializerInterface
{
    public function registerFormat($formatter, string $format = null): SerializerInterface;

    public function registerCustomHydrator(TypeAwareObjectHydrator $hydrator): SerializerInterface;

    public function registerCustomExtractor(TypeAwareObjectExtractor $extractor): SerializerInterface;

    public function serialize(string $format, $data, array &$context = []);

    public function deserialize(string $format, $object, $data, array &$context = []);
}