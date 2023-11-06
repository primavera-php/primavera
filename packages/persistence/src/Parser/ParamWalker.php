<?php

namespace Primavera\Persistence\Parser;

use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\ParamMetadata;

class ParamWalker
{
    private $paramNumber = -1;

    private $methodMetadata;

    public function __construct(MethodMetadata $methodMetadata)
    {
        $this->methodMetadata = $methodMetadata;
    }

    public static function create(MethodMetadata $methodMetadata): ParamWalker
    {
        return new self($methodMetadata);
    }

    public function next()
    {
        $this->paramNumber++;

        if (!isset($this->methodMetadata->params[$this->paramNumber])) {
            throw new \CompileError('number of fields doesn\'t match the number os method params');
        }

        return $this;
    }

    public function previous()
    {
        $this->paramNumber--;

        if ($this->paramNumber < -1) {
            throw new \CompileError('cant go back over -1 index');
        }

        return $this;
    }

    public function getParam(): ParamMetadata
    {
        return $this->methodMetadata->params[$this->paramNumber];
    }

    /**
     * @param string $attrName
     * @return \ReflectionAttribute[]
     */
    public function getParamAttributes(string $attrName): array
    {
        return $this->getParam()->reflection->getAttributes($attrName);
    }
}