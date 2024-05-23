<?php


namespace Primavera\Data;


use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Data\Mapping\Bindings;
use Primavera\Data\Mapping\Exclude;

class ObjectExtractor implements ComposableObjectExtractorInterface
{
    private MetadataFactoryInterface $metadataFactory;

    /**
     * @var TypeAwareObjectExtractor[]
     */
    private array $extractors = [];

    private string $defaultDateFormat;

    public function __construct(MetadataFactoryInterface $metadataFactory, string $defaultDateFormat = 'Y-m-d H:i:s')
    {
        $this->metadataFactory = $metadataFactory;
        $this->defaultDateFormat = $defaultDateFormat;
    }

    public function addExtractor(TypeAwareObjectExtractor $extractor) 
    {
        $this->extractors[$extractor->getSupportedClassName()] = $extractor;
    }

    protected function getExtractorForClass($class): ?TypeAwareObjectExtractor
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $metadata = $this->metadataFactory->getMetadataForClass($class);

        foreach (array_reverse([...$metadata->getHierarchy(), $class]) as $type) {
            if (isset($this->extractors[$type])) {
                return $this->extractors[$type];
            }
        }

        return null;
    }

    /**
     * @return array|string|null
     */
    public function extract($object, array &$context = []) 
    {
        if (is_iterable($object)) {
            return $this->extractIterable($object, $context);
        }

        $context['storage'] ??= new \SplObjectStorage();

        if ($context['storage']->contains($object)) {
            return $context['storage'][$object];
        }

        $context['storage']->attach($object);

        if ($extractor = $this->getExtractorForClass($object)) {
            return $extractor->extract($object, $context);
        }

        if ($object instanceof \DateTime) {
            return $this->extractDate($object, $context);
        }

        $data = [];

        $metadata = $this->metadataFactory->getMetadataForClass(get_class($object));

        /* @var $property PropertyMetadata */
        foreach($metadata->getPropertyMetadata() as $property) {
            if ($property->hasAnnotation(Exclude::class)) {
                continue;
            }

            $context['property'] = $property;
            $value = $property->hasGetter() ? $property->getter->invoke($object) : $property->getValue($object);

            $name = $property->hasAnnotation(Bindings::class)
                ? $property->getAnnotation(Bindings::class)->target
                    ?? $property->getAnnotation(Bindings::class)->source
                    ?? $property->name
                : $property->name;

            $data[$name] = !is_scalar($value) 
                && !is_bool($value) 
                && !is_null($value) 
                    ? $this->extract($value, $context) 
                    : $value;
        }

        if ($context['extractType'] ?? false) {
            $data[$context['typeField'] ?? '__type__'] = get_class($object);
        }

        $context['storage'][$object] = $data;

        return $data;
    }

    protected function extractDate(\DateTime $date, array &$context) 
    {
        /* @var $property PropertyMetadata */
        $property = $context['property'];
        $format = $this->defaultDateFormat;

        if ($property->isDateType() && $property->isDecoratedType()) {
            $format = $property->typeInfo['decoration'];
        }

        return $date->format($format);
    }

    protected function extractIterable(iterable $collection, array &$context): array 
    {
        $extracted = [];

        foreach ($collection as $index => $item) {
            if (is_iterable($item)) {
                $this->extractIterable($item, $context);
            } elseif (is_object($item)) {
                $extracted[$index] = $this->extract($item, $context);
            } else {
                $extracted[$index] = $item;
            }
        }

        return $extracted;
    }
}