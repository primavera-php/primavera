<?php

namespace Vox\Metadata\Reader;
use Symfony\Component\Yaml\Parser;

class YamlReader implements ReaderInterface
{
    private array $classYamls = [];

    private Parser $yamlParser;

    private string $path;

    public function __construct(
        private string $yamlExtension,
        string $path,
    ) {
        $this->path = realpath($path);
        $this->yamlParser = new Parser();
    }

    public function getClassAnnotations(\ReflectionClass $class) 
    {
        $yaml = $this->getClassYaml($class);

        return $this->getAnnotations($yaml, 'class');
    }
    
    public function getMethodAnnotations(\ReflectionMethod $method) 
    {
        $yaml = $this->getClassYaml($method->getDeclaringClass());

        return $this->getAnnotations($yaml, 'methods', $method->name);
    }
    
    public function getPropertyAnnotations(\ReflectionProperty $property) 
    {
        $yaml = $this->getClassYaml($property->getDeclaringClass());

        return $this->getAnnotations($yaml, 'properties', $property->name);
    }

    public function getClassYaml(\ReflectionClass $class)
    {
        return $this->classYamls[$class->name] ??= $this->loadYml($class);
    }

    private function loadYml(\ReflectionClass $class)
    {
        $className = $class->getName();
        
        $path = sprintf(
            '%s/%s.%s',
            preg_replace('/\/$/', '', $this->path), 
            str_replace('\\', '.', $className),
            $this->yamlExtension
        );

        if (is_file($path)) {
            return $this->yamlParser->parse(file_get_contents($path));
        }
        
        $path = sprintf(
            '%s/%s.%s',
            preg_replace('/\/$/', '', $this->path), 
            str_replace('\\', '.', $className),
            $this->yamlExtension
        );
        
        if (is_file($path)) {
            return $this->yamlParser->parse(file_get_contents($path));
        }

        return [];
    }

    /**
     * @return object[]
     */
    private function getAnnotations(array $yaml, string $key, string $name = null): array {
        $annotations = [];

        $data = $name ? $yaml[$key][$name] ?? [] : $yaml[$key] ?? [];

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $annotationClass = $value;
                $params = [];
            } else {
                $annotationClass = $key;
                $params = $value;
            }

            $ref = new \ReflectionClass($annotationClass);
            $ctorParams = [];

            if ($ctor = $ref->getConstructor()) {
                foreach ($ctor->getParameters() as $param) {
                    if (!isset($params[$param->name]))
                        continue;

                    $ctorParams[$param->name] = $params[$param->name];
                    unset($params[$param->name]);
                }
            }

            $annotation = $ref->newInstanceArgs($ctorParams);

            foreach ($params as $key => $value) {
                $annotation->{$key} = $value;
            }

            $annotations[$annotationClass] = $annotation;
        }

        return $annotations;
    }
}
