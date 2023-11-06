<?php

namespace Primavera\Metadata;

interface ClassComponentMetadataInterface extends MetadataInterface, TypedComponentMetadataInterface
{
    public function getClass(): string;
}