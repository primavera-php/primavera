<?php

namespace Primavera\Metadata\Driver;

use ProxyManager\Proxy\AccessInterceptorValueHolderInterface;
use ReflectionClass;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\MethodMetadataInterface;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\Reader\ReaderInterface;

/**
 * Driver to create classes metadata using annotations, depends on doctrine's annotation reader
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 * 
 * @template T of ClassMetadataInterface<P,M>
 * @template P of PropertyMetadata
 * @template M of MethodMetadataInterface
 */
class AnnotationDriver implements DriverInterface
{
    private ReflectionClass $classMetadataClass;
    private ReflectionClass $propertyMetadataClass;
    private ReflectionClass $methodMetadataClass;
    
    /**
     * @param class-string<T> $classMetadataClassName
     * @param class-string<P> $propertyMetadataClassName
     * @param class-string<M> $methodMetadataClassName
     */
    public function __construct(
        private ReaderInterface $annotationReader,
        string $classMetadataClassName = ClassMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class
    ) { 
        $this->classMetadataClass = new ReflectionClass($classMetadataClassName);
        $this->propertyMetadataClass = new ReflectionClass($propertyMetadataClassName);
        $this->methodMetadataClass = new ReflectionClass($methodMetadataClassName);
    }
    
    /**
     * @return T
     */
    public function loadMetadataForClass(ReflectionClass $class): ClassMetadataInterface
    {
        if ($class->implementsInterface(AccessInterceptorValueHolderInterface::class)) {
            $class = $class->getParentClass();
        }

        /* @var $classMetadata ClassMetadata */
        $classMetadata    = $this->classMetadataClass->newInstance($class);
        $classAnnotations = $this->annotationReader->getClassAnnotations($class);

        $classMetadata->setAnnotations($classAnnotations);
        
        foreach ($class->getMethods() as $method) {
            $methodMatadata = $this->methodMetadataClass->newInstance($method);
            $methodMatadata->setAnnotations($this->annotationReader->getMethodAnnotations($method));
            $classMetadata->addMethodMetadata($methodMatadata);
        }
        
        foreach ($class->getProperties() as $property) {
            $params = [$property];

            if ($this->propertyMetadataClass->isSubclassOf(PropertyMetadata::class) 
                || $this->propertyMetadataClass->name == PropertyMetadata::class) {
                $params = array_merge($params, $this->parseAccessors($property, $classMetadata));
            }

            $propertyMetadata = $this->propertyMetadataClass->newInstanceArgs($params);
            $propertyMetadata->setAnnotations($this->annotationReader->getPropertyAnnotations($property));

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }
        
        return $classMetadata;
    }

    private function parseAccessors(\ReflectionProperty $propertyMetadata, ClassMetadata $classMetadata) {
        return [
            $this->getGetter($propertyMetadata, $classMetadata),
            $this->getSetter($propertyMetadata, $classMetadata),
            $this->getAdder($propertyMetadata, $classMetadata),
        ];
    }

    private function getSetter(\ReflectionProperty $propertyMetadata, ClassMetadata $classMetadata) {
        $setterName = sprintf('set%s', ucfirst($propertyMetadata->name));

        return $classMetadata->methodMetadata[$setterName] ?? null;
    }

    private function getGetter(\ReflectionProperty $propertyMetadata, ClassMetadata $classMetadata) {
        $getterName = sprintf('get%s', ucfirst($propertyMetadata->name));

        return $classMetadata->methodMetadata[$getterName] ?? null;
    }

    private function getAdder(\ReflectionProperty $propertyMetadata, ClassMetadata $classMetadata) {
        $adderName = sprintf('add%s', ucfirst($propertyMetadata->name));

        return $classMetadata->methodMetadata[$adderName] ?? null;
    }

    public function __serialize()
    {
        return [
            $this->classMetadataClass->name,
            $this->propertyMetadataClass->name,
            $this->methodMetadataClass->name,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->classMetadataClass = new ReflectionClass($data[0]);
        $this->propertyMetadataClass = new ReflectionClass($data[1]);
        $this->methodMetadataClass = new ReflectionClass($data[2]);
    }
}
