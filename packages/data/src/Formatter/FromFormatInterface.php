<?php


namespace Primavera\Data\Formatter;


interface FromFormatInterface
{
    public function fromFormat($data, array &$context = []);
}