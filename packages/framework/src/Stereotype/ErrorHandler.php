<?php

namespace Vox\Framework\Stereotype;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ErrorHandler {
    
    /**
     * @var string
     */
    public $priotiry = 1;
}
