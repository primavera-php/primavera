<?php

namespace Primavera\Metadata\Test;

use PHPUnit\Framework\TestCase;
use Primavera\Metadata\PropertyMetadata;

class PropertyMetadataTest extends TestCase
{
    private \ReflectionClass $myEventReflection;

    public function setUp(): void
    {
        $this->myEventReflection = new \ReflectionClass(MyEvent::class);
    }

    public function testShouldParseDateTime() {
        $metadata = new PropertyMetadata($this->myEventReflection->getProperty('createdAt'), null, null, null);

        $this->assertEquals(\DateTime::class, $metadata->typeInfo['class']);
        $this->assertEquals('Y-m-d H:i:s', $metadata->typeInfo['decoration']);
        $this->assertTrue($metadata->isDecoratedType());
        $this->assertTrue($metadata->isDateType());
    }

    public function testShouldParseDecorationColections() {
        $metadata1 = new PropertyMetadata($this->myEventReflection->getProperty('dates'), null, null, null);

        $this->assertEquals('array', $metadata1->typeInfo['class']);
        $this->assertEquals(\DateTime::class, $metadata1->typeInfo['decoration']);

        $metadata2 = new PropertyMetadata($this->myEventReflection->getProperty('dates2'), null, null, null);

        $this->assertEquals('array', $metadata2->typeInfo['class']);
        $this->assertEquals(\DateTime::class, $metadata2->typeInfo['decoration']);

        $metadata3 = new PropertyMetadata($this->myEventReflection->getProperty('relations'), null, null, null);

        $this->assertEquals('array', $metadata3->typeInfo['class']);
        $this->assertEquals(MyEvent::class, $metadata3->typeInfo['decoration']);

        $metadata4 = new PropertyMetadata($this->myEventReflection->getProperty('relations2'), null, null, null);

        $this->assertEquals(\Iterator::class, $metadata4->typeInfo['class']);
        $this->assertEquals(MyEvent::class, $metadata4->typeInfo['decoration']);
    }
}

class MyEvent {
    /**
     * @var \DateTime<Y-m-d H:i:s>
     */
    private \DateTime $createdAt;

    /**
     * @var array<\DateTime>
     */
    private array $dates;

    /**
     * @var \DateTime[]
     */
    private array $dates2;

    /**
     * @var array<MyEvent>
     */
    private $relations;

    /**
     * @var \Iterator<MyEvent>
     */
    private $relations2;

    public function __construct(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}