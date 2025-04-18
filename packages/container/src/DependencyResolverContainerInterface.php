<?php

namespace Primavera\Container;

use Primavera\Metadata\ParamMetadataInterface;

interface DependencyResolverContainerInterface
{
    public function get(string $id, ParamMetadataInterface $paramMetadata = null);
}
