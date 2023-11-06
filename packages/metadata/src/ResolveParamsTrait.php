<?php

namespace Primavera\Metadata;

/**
 * @template T of ParamMetadata
 */
trait ResolveParamsTrait
{
    /**
     * @var T[]
     */
    public array $params = [];

    protected static string $paramMetadataClass = ParamMetadata::class;

    public function resolveParams()
    {
        $reflection = $this->getReflection();

        foreach ($reflection->getParameters() as $parameter) {
            $this->params[] = new self::$paramMetadataClass($parameter, $this);
        }
    }

    #[\ReturnTypeWillChange]
    abstract public function getReflection(): \ReflectionMethod | \ReflectionFunction;

    /**
     * @return T[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param class-string<T> $paramMetadataClass
     */
    public static function setParamMetadataClass(string $paramMetadataClass)
    {
        self::$paramMetadataClass = $paramMetadataClass;
    }
}
