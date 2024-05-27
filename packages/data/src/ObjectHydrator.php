<?php

namespace Primavera\Data;

use DateTime;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Metadata\TypeHelper;
use RuntimeException;
use Primavera\Data\Mapping\Bindings;
use Primavera\Data\Mapping\Discriminator;
use Primavera\Metadata\ClassMetadata;

/**
 * Hydrates objects based on its metadata information, uses data mapping
 * 
 * @author Jhonatan Teixeira <jhonatan.teixeira@gmail.com>
 */
class ObjectHydrator implements ComposableObjectHydratorInterface
{
    /**
     * @var TypeAwareObjectHydrator[]
     */
    private array $hydrators = [];

    private PropertyAccessorInterface $propertyAcessor;
    
    public function __construct(
        private MetadataFactoryInterface $metadataFactory,
    ) {
        $this->propertyAcessor = new PropertyAccessor($this->metadataFactory);
    }

    public function addHydrator(TypeAwareObjectHydrator $hydrator) 
    {
        $this->hydrators[$hydrator->getSupportedClassName()] = $hydrator;
    }
   
    public function hydrate($object, $data, array &$context = null): object | array
    {
        if (is_null($context)) {
            $context = [];
        }

        $type = new TypeHelper(is_string($object) ? $object : $object::class);

        if ($type->isDecoratedType()) {
            return $this->convertDecorated($type->getType(), $data);
        }

        if ($type->isNativeType()) {
            return $this->convertNativeType($type->getType(), $data);
        }

        $metadata = $this->metadataFactory->getMetadataForClass($type->getType());

        if (is_string($object)) {
            $object = $metadata->getReflection()->newInstanceWithoutConstructor();
        }

        $context['object'] = $object;
        $context['hydrator'] = $this;
        $context['metadata'] = $metadata;

        if (is_array($data) && array_is_list($data)) {
            return $this->hydrateList($object, $data, $context);
        }

        if ($hydrator = $this->getHydratorForClass($type->getType())) {
            return $hydrator->hydrate($object, $data, $context);
        }

        foreach ($metadata->getPropertyMetadata() as $property) {
            $annotation = $property->getAnnotation(Bindings::class);
            $source = $annotation ? ($annotation->source ?? $property->getName()) : $property->getName();
            $type = $property->type;
            $context['property'] = $property;
            $value = $data[$source] ?? null;

            $objPropertyData = null;

            if (class_exists($property->getType()) || interface_exists($property->getType())) {
                $objPropertyData = $this->propertyAcessor->tryGet($object, $property->getName());
            }

            if (!$value) {
                continue;
            }

            $value = match(true) {
                $property->isDecoratedType() => $this->convertDecorated($property->type, $value, $objPropertyData, $context),
                $property->isNativeType() => $this->convertNativeType($type, $value),
                default => $this->convertObjectValue($type, $value, $objPropertyData, $context)
            };

            if (
                $property->isParsedType()
                && $property->isDecoratedType()
                && $property->hasRealType() 
                && $property->getRealType() != ($property->getTypeInfo()['class'])
            ) {
                $value = $this->convertObjectValue($property->getRealType(), $value, $objPropertyData, $context);
            }

            $this->propertyAcessor->set($object, $property->getName(), $value);
        }

        return $object;
    }

    private function getHydratorForClass($class): ?TypeAwareObjectHydrator
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
    
    private function convertDecorated(string $type, $value, object $object = null, &$context = null)
    {
        preg_match('/(?P<class>.*)((\<(?P<decoration>.*)\>)|(?P<brackets>\[\]))/', $type, $matches);
        
        $class      = isset($matches['brackets']) ? 'array' : $matches['class'];
        $decoration = isset($matches['brackets']) ? $matches['class'] : $matches['decoration'];

        $convertItem = function($item) use ($decoration, $object) {
            if (is_scalar($item) || is_bool($item) || $item instanceof DateTime) {
                return $this->convertNativeType($decoration, $item);
            }
            
            return $this->convertObjectValue($decoration, $item, $object, $context);
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
    
    private function convertObjectValue(string $type, $data, object $object = null, array &$context = null)
    {
        $metadata = $this->getObjectMetadata($type, $data);

        if (!$object) {
            $object = $metadata->getReflection()->newInstanceWithoutConstructor();
        }

        if ($hydrator = $this->getHydratorForClass($type)) {
            return $hydrator->hydrate($object, $data, $context);
        }

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
