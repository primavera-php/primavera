<?php

namespace Vox\Metadata\Driver;

use Vox\Metadata\ClassMetadataInterface;

interface DriverInterface
{
    public function loadMetadataForClass(\ReflectionClass $class): ClassMetadataInterface;
}
