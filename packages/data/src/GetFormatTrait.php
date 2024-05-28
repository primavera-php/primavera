<?php

namespace Primavera\Data;


trait GetFormatTrait
{
    private function getFormat(string $format)
    {
        $formats = [
            'application/xml' => 'xml',
            'application/json' => 'json',
            'application/yaml' => 'yaml',
        ];

        return $formats[$format] ?? $format;
    }
}