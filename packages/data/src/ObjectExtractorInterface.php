<?php


namespace Primavera\Data;


interface ObjectExtractorInterface
{
    public function extract($object, array &$context = []);
}