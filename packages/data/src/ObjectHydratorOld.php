<?php

namespace Primavera\Data;

use DateTime;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\TypeHelper;
use RuntimeException;
use Primavera\Data\Mapping\Bindings;
use Primavera\Data\Mapping\Discriminator;
use Primavera\Data\Mapping\Exclude;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\PropertyMetadata;

/**
 * Hydrates objects based on its metadata information, uses data mapping
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class ObjectHydrator implements ComposableObjectHydratorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var TypeAwareObjectHydrator[]
     */
    private array $hydrators = [];
    
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function addHydrator(TypeAwareObjectHydrator $hydrator) 
    {
        $this->hydrators[$hydrator->getSupportedClassName()] = $hydrator;
    }

    protected function getHydratorForClass($class): ?TypeAwareObjectHydrator
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $metadata = $this->metadataFactory->getMetadataForClass($class);

        foreach (array_reverse([...$metadata->getHierarchy(), $class]) as $type) {
            if (isset($this->hydrators[$type])) {
                return $this->hydrators[$type];
            }
        }

        return null;
    }
    
    public function hydrate($object, $data, array &$context = null): object | array
    {
        if (is_null($context)) {
            $context = [];
        }

        $context['hydrator'] = $this;

        if (is_string($object)) {
            $objectType = new TypeHelper($object);
            $object = $objectType->getReflection()->newInstanceWithoutConstructor();
        } else {
            $objectType = new TypeHelper($object::class);
        }

        $context['object'] = $object;

        if ($objectType->isDecoratedType()) {
            return $this->convertDecorated($objectType->getType(), $data);
        }

        if ($objectType->isNativeType()) {
            return $this->convertNativeType($objectType->getType(), $data);
        }

        if (is_array($data) && array_is_list($data)) {
            return $this->hydrateList($object, $data, $context);
        }

        if ($hydrator = $this->getHydratorForClass($object)) {
            return $hydrator->hydrate($object, $data, $context);
        }

        $objectMetadata = $this->getObjectMetadata($object, $data);

        /* @var $propertyMetadata PropertyMetadata  */
        foreach ($objectMetadata->getPropertyMetadata() as $propertyMetadata) {
            $annotation = $propertyMetadata->getAnnotation(Bindings::class);
            $source     = $annotation ? ($annotation->source ?? $propertyMetadata->name) : $propertyMetadata->name;
            $type       = $propertyMetadata->type;
            $context['property'] = $propertyMetadata;
            
            if (!isset($data[$source]) 
                || ($propertyMetadata->hasAnnotation(Exclude::class) 
                    && $propertyMetadata->getAnnotation(Exclude::class)->input)) {
                continue;
            }
            
            $value = $data[$source];

            if ($type && !is_null($value)) {
                if ($propertyMetadata->isDecoratedType()) {
                    $value = $this->convertDecorated($type, $value);
                } elseif ($propertyMetadata->isNativeType()) {
                    $value = $this->convertNativeType($type, $value);
                } else {
                    if (!class_exists($type, true) && !interface_exists($type, true)) {
                        throw new RuntimeException("type $type don't exists");
                    }
                    
                    $value = $this->convertObjectValue($type, $value, $context);
                }

                if (
                    $propertyMetadata->isParsedType()
                    && $propertyMetadata->isDecoratedType()
                    && $propertyMetadata->hasRealType() 
                    && $propertyMetadata->getRealType() != ($propertyMetadata->getTypeInfo()['class'])
                ) {
                    $value = $this->convertObjectValue($propertyMetadata->getRealType(), $value, $context);
                }
            }

            if ($propertyMetadata->hasSetter()) {
                $propertyMetadata->setter->invoke($object, $value);
            } else {
                $propertyMetadata->setValue($object, $value);
            }
        }

        return $object;
    }

    protected function hydrateList($type, array $list, array &$context = null): array
    {
        $hydrated = [];

        foreach($list as $data) {
            $hydrated[] = $this->hydrate($type, $data, $context);
        }

        return $hydrated;
    }
    
    private function convertNativeType($type, $value)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'string':
                return (string) $value;
            case 'boolean':
            case 'bool':
                return (bool) $value;
            case 'array':
                if (!is_array($value)) {
                    throw new RuntimeException('value is not array');
                }
                
                return $value;
            case 'DateTime':
            case '\DateTime':
                return new DateTime($value);
            case 'DateTimeImmutable':
            case '\DateTimeImmutable':
                return new \DateTimeImmutable($value);
            default:
                return $value;
        }
    }
    
    private function convertDecorated(string $type, $value)
    {
        preg_match('/(?P<class>.*)((\<(?P<decoration>.*)\>)|(?P<brackets>\[\]))/', $type, $matches);
        
        $class      = isset($matches['brackets']) ? 'array' : $matches['class'];
        $decoration = isset($matches['brackets']) ? $matches['class'] : $matches['decoration'];

        $convertItem = function($item) use ($decoration) {
            if (is_scalar($item) || is_bool($item) || $item instanceof DateTime) {
                return $this->convertNativeType($decoration, $item);
            }
            
            return $this->convertObjectValue($decoration, $item);
        };

        $populateData = function ($value) use($convertItem) {
            return array_map($convertItem, $value);
        };

        switch ($class) {
            case 'array':
                if (!is_array($value)) {
                    throw new RuntimeException('value mapped as array is not array');
                }

                $data = $populateData($value);

                break;
            case 'DateTime':
            case '\DateTime':
                $data = DateTime::createFromFormat($decoration, $value);

                if (!$data) {
                    throw new RuntimeException("cannot convert date $value to format $decoration");
                }

                break;
            case 'DateTimeImmutable':
            case '\DateTimeImmutable':
                $data = \DateTimeImmutable::createFromFormat($decoration, $value);

                if (!$data) {
                    throw new RuntimeException("cannot convert date $value to format $decoration");
                }

                break;
            default:
                if (is_array($value)) {
                    $data = $populateData($value);
                } else {
                    $data = $convertItem($value);
                }

                $data = $this->convertObjectValue($class, $data);
        }

        return $data;
    }
    
    private function convertObjectValue(string $type, $data, array &$context = null)
    {
        if ($hydrator = $this->getHydratorForClass($type)) {
            return $hydrator->hydrate($type, $data, $context);
        }

        $metadata = $this->getObjectMetadata($type, $data);
        $object   = $metadata->getReflection()->newInstanceWithoutConstructor();

        $this->hydrate($object, $data, $context);

        return $object;
    }
    
    private function getObjectMetadata($object, $data): ClassMetadata
    {
        $metadata      = $this->metadataFactory->getMetadataForClass(is_string($object) ? $object : get_class($object));
        $discriminator = $metadata->getAnnotation(Discriminator::class);

        if (is_array($data) && $discriminator instanceof Discriminator && isset($data[$discriminator->field])) {
            if (!isset($discriminator->map[$data[$discriminator->field]])) {
                throw new RuntimeException("no discrimination for {$data[$discriminator->field]}");
            }

            $type     = $discriminator->map[$data[$discriminator->field]];
            $metadata = $this->metadataFactory->getMetadataForClass($type);
        }
        
        return $metadata;
    }
}
