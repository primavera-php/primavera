<?php


namespace Primavera\Data;


use InvalidArgumentException;
use Primavera\Data\Formatter\FormatAwareInterface;
use Primavera\Data\Formatter\FromFormatInterface;
use Primavera\Data\Formatter\ToFormatInterface;

class Serializer implements SerializerInterface
{
    use GetFormatTrait;

    private array $formats = [];

    private ComposableObjectExtractorInterface $extractor;

    private ComposableObjectHydratorInterface $hydrator;

    /**
     * Serializer constructor.
     * @param ObjectExtractorInterface $extractor
     * @param ObjectHydratorInterface $hydrator
     */
    public function __construct(ObjectExtractorInterface $extractor, ObjectHydratorInterface $hydrator)
    {
        $this->extractor = $extractor;
        $this->hydrator = $hydrator;
    }

    public function registerFormat($formatter, string $format = null): SerializerInterface
    {
        if ($formatter instanceof FormatAwareInterface) {
            $format = $formatter->getFormatName();
        }

        if (!$format) {
            throw new InvalidArgumentException("format must be informed or formatter implements FormatAwareInterface");
        }

        if (!$formatter instanceof ToFormatInterface || !$formatter instanceof FromFormatInterface) {
            throw new InvalidArgumentException("formatter must implement formatter interfaces");
        }

        $this->formats[$format] = $formatter;

        return $this;
    }

    public function registerCustomHydrator(TypeAwareObjectHydrator $hydrator): SerializerInterface
    {
        $this->hydrator->addHydrator($hydrator);

        return $this;
    }

    public function registerCustomExtractor(TypeAwareObjectExtractor $extractor): SerializerInterface
    {
        $this->extractor->addExtractor($extractor);

        return $this;
    }


    public function serialize(string $format, $data, array &$context = []) {
        $data = $this->extractor->extract($data, $context);
        $format = $this->getFormat($format);

        $this->checkFormatter($format, true, false);

        return $this->formats[$format]->toFormat($data, $context);
    }

    public function deserialize(string $format, $object, $data, array &$context = []) {
        $format = $this->getFormat($format);

        $this->checkFormatter($format, false, true);

        $data = $this->formats[$format]->fromFormat($data, $context);

        return $this->hydrator->hydrate($object, $data, $context);
    }

    private function checkFormatter(string $format, $isSerializer = true, $isDeserializer = true) {
        assert(array_key_exists($format, $this->formats), "format $format not registered");

        if ($isSerializer)
            assert(
                $this->formats[$format] instanceof ToFormatInterface,
                sprintf("%s is not a serializer", get_class($this->formats[$format]))
            );

        if ($isDeserializer)
            assert(
                $this->formats[$format] instanceof FromFormatInterface,
                sprintf("%s is not a deserializer", get_class($this->formats[$format]))
            );
    }
}