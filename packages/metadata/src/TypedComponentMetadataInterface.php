<?php

namespace Primavera\Metadata;

interface TypedComponentMetadataInterface extends MetadataInterface
{
    public function getType(): string | array | null;

    public function isParsedType(): bool;

    public function hasRealType(): bool;

    public function getRealType(): null | string | array;

    public function getTypeInfo(): ?array;
}