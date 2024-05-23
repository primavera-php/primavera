<?php


namespace Primavera\Data;


interface ComposableObjectExtractorInterface extends ObjectExtractorInterface
{
    public function addExtractor(TypeAwareObjectExtractor $extractor);
}