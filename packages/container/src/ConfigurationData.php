<?php

namespace Primavera\Container;

use Symfony\Component\Yaml\Yaml;

class ConfigurationData implements \IteratorAggregate
{
    /**
     * @var ConfigurationItem[]
     */
    private array $data;

    public function __construct(private string | array $filenameOrData)
    {
        if (is_string($filenameOrData)) {
            $this->data = $this->buildData(Yaml::parseFile($filenameOrData));
        } else {
            $this->data = $filenameOrData;
        }
    }

    private function buildData($data, string $parent = null)
    {
        $config = [];

        foreach ($data as $name => $item) {
            if (is_array($item)) {
                $config[$name] = new self($this->buildData($item, is_null($parent) ? $name : "$parent.$name"));

                continue;
            }

            $config[$name] = new ConfigurationItem($name, $parent, $item);
        }

        return $config;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getIterator()
    {
        return new \RecursiveIteratorIterator(
            new class($this->data) extends \RecursiveArrayIterator {
                public function hasChildren(): bool
                {
                    return parent::current() instanceof ConfigurationData;
                }

                public function getChildren() 
                {
                    return new self(parent::current()->getData());
                }

                public function key()
                {
                    return parent::current()->getPath();
                }

                public function current()
                {
                    return parent::current()->getValue();
                }
            },
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
    }

    public function __get($name)
    {
        $value = $this->data[$name] ?? null;

        if ($value instanceof self) {
            return $value;
        }

        if ($value instanceof ConfigurationItem) {
            return $value->getValue();
        }

        if (!$value) {
            return null;
        }

        throw new \InvalidArgumentException("cannot find config {$name} or its on an invalid type");
    }
}