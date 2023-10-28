<?php

namespace Vox\Metadata;

interface TypedComponentMetadataInterface extends MetadataInterface
{
    public function getType(): string | array | null;
}