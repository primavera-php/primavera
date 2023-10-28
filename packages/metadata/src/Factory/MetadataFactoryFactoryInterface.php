<?php

namespace Vox\Metadata\Factory;

use Psr\SimpleCache\CacheInterface;
use Vox\Metadata\ClassMetadata;
use Vox\Metadata\MethodMetadata;
use Vox\Metadata\PropertyMetadata;

/**
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
interface MetadataFactoryFactoryInterface
{
    /**
     * @param string $metadataClassName the fqcn to be used as class metadata holder, must implement the interface
     * @param string $methodMetadataClassName the fqcn to be used as method metadata holder, must implement the interface
     * @param string $propertyMetadataClassName the fqcn to be used as property metadata holder, must implement the interface
     *
     * @return MetadataFactoryInterface
     */
    public function createAnnotationMetadataFactory(
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        CacheInterface $cache = null,
    ): MetadataFactoryInterface;

    /**
     * @param string $metadataPath the path for the folder containing the metadata yaml files
     * @param string $metadataClassName the fqcn to be used as class metadata holder, must implement the interface
     * @param string $methodMetadataClassName the fqcn to be used as method metadata holder, must implement the interface
     * @param string $propertyMetadataClassName the fqcn to be used as property metadata holder, must implement the interface
     * @param string $yamlExtension the desired extension for the yaml files
     *
     * @return MetadataFactoryInterface
     */
    public function createYmlMetadataFactory(
        string $metadataPath,
        string $yamlExtension = 'yaml',
        string $metadataClassName = ClassMetadata::class,
        string $methodMetadataClassName = MethodMetadata::class,
        string $propertyMetadataClassName = PropertyMetadata::class,
        CacheInterface $cache = null,
    ): MetadataFactoryInterface;
}
