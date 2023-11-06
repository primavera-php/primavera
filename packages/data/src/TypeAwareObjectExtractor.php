<?php

namespace Primavera\Data;

interface TypeAwareObjectExtractor extends ObjectExtractorInterface
{
    public function getSupportedClassName(): string;
}