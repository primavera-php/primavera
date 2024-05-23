<?php

namespace Primavera\Data;

use PHPUnit\Framework\TestCase;
use Primavera\Data\Formatter\JsonFormatter;
use Primavera\Metadata\Factory\MetadataFactoryFactory;

class SerializerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $customDateMapper = new CustomDateMapper();
        $mf = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();
        $this->serializer = new Serializer(new ObjectExtractor($mf), new ObjectHydrator($mf));
        $this->serializer->registerFormat(new JsonFormatter())
            ->registerCustomHydrator($customDateMapper)
            ->registerCustomExtractor($customDateMapper);
    }

    public function testShouldSerializeToJson() {
        [$data, $expected] = $this->createData();

        $this->assertEquals($expected, $this->serializer->serialize('json', $data));
        $this->assertEquals($data, $this->serializer->deserialize('json', SerializeStub::class, $expected));
    }

    public function testShouldSerializeListToJson() 
    {
        [$data, $expected] = $this->createData();

        $data = array_fill(0, 10, $data);
        $expected = json_encode(array_fill(0, 10, json_decode($expected, true)));

        $this->assertEquals($expected, $this->serializer->serialize('json', $data));
        $this->assertEquals($data, $this->serializer->deserialize('json', SerializeStub::class, $expected));
    }

    public function createData() {
        return [
            new SerializeStub(10, 'jhon', 33, new CustomDate('20', '12', '1983')),
            '{"id":10,"name":"jhon","age":33,"date":"20-12-1983"}'
        ];
    }
}

class SerializeStub {
    private int $id;

    private string $name;

    private int $age;

    private CustomDate $date;

    public function __construct(int $id, string $name, int $age, CustomDate $date)
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->date = $date;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }
}

class CustomDate
{
    public function __construct(
        public string $day,
        public string $month,
        public string $year,
    ) {}
}

class CustomDateMapper implements TypeAwareObjectExtractor, TypeAwareObjectHydrator
{
    public function getSupportedClassName(): string
    {
        return CustomDate::class;
    }

    /**
     * @param CustomDate $object
     */
    public function extract($object, array &$context = [])
    {
        return implode('-', [$object->day, $object->month, $object->year]);
    }

    public function hydrate($object, $data, array &$context = null): array | object
    {
        return new CustomDate(...explode('-', $data));
    }
}