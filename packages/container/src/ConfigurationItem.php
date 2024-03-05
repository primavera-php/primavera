<?php 

namespace Primavera\Container;


class ConfigurationItem
{
    private $value;

    private string $path;

    public function __construct(
        private string $key,
        ?string $path,
        $value,
    ) {
        $this->path = $path ? "{$path}.{$key}" : $key;

        $env = strtoupper(str_replace('.', '_', $this->path));

        if ($envValue = getenv($env)) {
            if (is_numeric($envValue)) {
                if (ctype_digit($envValue)) {
                    $value = (int) $envValue;
                } else {
                    $value = (float) $envValue;
                }
            } elseif ($envValue === 'true') {
                $value = true;
            } elseif ($envValue === 'false') {
                $value = false;
            } else {
                $value = $envValue;
            }
        }

        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getPath()
    {
        return $this->path;
    }
}