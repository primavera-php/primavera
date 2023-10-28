<?php

namespace Vox\Metadata;

interface ClassComponentMetadataInterface extends MetadataInterface, TypedComponentMetadataInterface
{
    public function getClass(): string;
}