<?php


namespace Primavera\Http\Stereotype;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @NamedArgumentConstructor
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class HttpClient
{
    /**
     * @var string
     * @required
     */
    public $uri;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }
}
