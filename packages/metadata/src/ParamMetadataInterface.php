<?php

namespace Primavera\Metadata;

interface ParamMetadataInterface extends TypedComponentMetadataInterface
{
    public function getClass(): ?string;

    public function getFunction(): string;

}