<?php

namespace Primavera\Metadata;

interface InvokableMetadataInterface
{
    public function invoke(...$rags): mixed;

    /**
     * @return TypedComponentMetadataInterface[]
     */
    public function getParams(): array;
}
