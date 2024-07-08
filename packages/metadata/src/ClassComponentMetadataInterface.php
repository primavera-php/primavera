<?php

namespace Primavera\Metadata;

interface ClassComponentMetadataInterface extends MetadataInterface, TypedComponentMetadataInterface
{
    public function getClass(): string;

    public function isStatic(): bool;
}