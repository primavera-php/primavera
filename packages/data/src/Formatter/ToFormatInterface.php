<?php


namespace Primavera\Data\Formatter;


interface ToFormatInterface
{
    public function toFormat($data, array &$context = []);
}