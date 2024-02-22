<?php

namespace Primavera\Framework\Stereotype;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ErrorHandler {
    
    /**
     * @var string
     */
    public $priotiry = 1;
}
