<?php

namespace Primavera\Metadata;

interface InvokableMetadataInterface
{
    public function invoke(...$rags): mixed;

    /**
     * @return ParamMetadata[]
     */
    public function getParams(): array;
}
