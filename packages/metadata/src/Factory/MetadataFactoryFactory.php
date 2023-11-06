<?php

namespace Primavera\Metadata\Factory;

use Primavera\Metadata\Cache\PsrSimpleCacheAdapter;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\Driver\AnnotationDriver;
use Primavera\Metadata\Driver\DriverInterface;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\Reader\AttributeReader;
use Primavera\Metadata\Reader\YamlReader;
use Psr\SimpleCache\CacheInterface;

class MetadataFactoryFactory implements MetadataFactoryFactoryInterface
{
    private bool $debug;

    /**
     * @param bool $debug whether to enable debug or not
     */
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function createAnnotationMetadataFactory(
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        CacheInterface $cache = null,
    ): MetadataFactoryInterface {
        $factory = new MetadataFactory(
            $this->createAnnotationMetadataDriver(
                $metadataClassName,
                $methodMetadataClassName,
                $propertyMetadataClassName
            ),
            $cache,
        );

        return $factory;
    }
    
    private function createAnnotationMetadataDriver(
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
    ): DriverInterface {
        return new AnnotationDriver(
            new AttributeReader(),
            $metadataClassName,
            $propertyMetadataClassName,
            $methodMetadataClassName
        );
    }

    public function createYmlMetadataFactory(
        string $metadataPath,
        string $yamlExtension = 'yaml',
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        CacheInterface $cache = null,
    ): MetadataFactoryInterface {
        $factory = new MetadataFactory(
            $this->createYmlMetadataDriver(
                $metadataPath,
                $metadataClassName,
                $methodMetadataClassName,
                $propertyMetadataClassName,
                $yamlExtension
            ),
            $cache,
        );
        
        return $factory;
    }

    private function createYmlMetadataDriver(
        string $metadataPath,
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        string $yamlExtension = 'yaml'
    ): DriverInterface {
        return new AnnotationDriver(
            new YamlReader(
                $yamlExtension,
                $metadataPath,
            ),
            $metadataClassName, 
            $propertyMetadataClassName, 
            $methodMetadataClassName,
        );
    }
}
