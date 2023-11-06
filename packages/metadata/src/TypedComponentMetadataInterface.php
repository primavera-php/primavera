<?php

namespace Primavera\Metadata;

interface TypedComponentMetadataInterface extends MetadataInterface
{
    public function getType(): string | array | null;
}