<?php


namespace Primavera\Metadata\Test\Factory;

use PHPUnit\Framework\TestCase;
use Primavera\Metadata\ClassMetadata;
use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\Test\Stub\TestAnnotation;

class MetadataFactoryTest extends TestCase
{
    public function testShouldReadMetadata() {
        $factory = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();

        $metadata = $factory->getMetadataForClass(MetadataStub::class);

        $this->doAssertions($metadata, 'some');
    }

    public function testShouldSerializeMetadata() {
        $factory = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();

        $metadata = $factory->getMetadataForClass(MetadataStub::class);

        $serialized = serialize($metadata);

        $this->doAssertions(unserialize($serialized), 'some');
    }

    public function testShouldReadYamlMetadata() {
        $factory = (new MetadataFactoryFactory())->createYmlMetadataFactory(__DIR__ . '/../fixtures');

        $metadata = $factory->getMetadataForClass(MetadataStubYaml::class);

        $serialized = serialize($metadata);

        $this->doAssertions($metadata = unserialize($serialized));
        $this->assertEquals($metadata->propertyMetadata['name']->getAnnotation(TestAnnotation::class)->name, 'power');
        $this->assertEquals($metadata->propertyMetadata['name']->getAnnotation(TestAnnotation::class)->test, 'more');
    }
    
    public function testShouldReadAttribute() {
        $factory = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();

        $metadata = $factory->getMetadataForClass(AttributeStub::class);

        $serialized = serialize($metadata);

        $this->doAssertions(unserialize($serialized), 'my name');
    }

    public function doAssertions(ClassMetadata $metadata, $name = 'default') {
        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertTrue($metadata->hasAnnotation(TestAnnotation::class));
        $this->assertTrue($metadata->methodMetadata['getId']->hasAnnotation(TestAnnotation::class));
        $this->assertEquals('int', $metadata->methodMetadata['getId']->type);
        $this->assertEquals('int', $metadata->propertyMetadata['id']->type);
        $this->assertEquals('string', $metadata->propertyMetadata['name']->type);
        $this->assertFalse($metadata->propertyMetadata['id']->hasAnnotation(TestAnnotation::class));
        $this->assertTrue($metadata->propertyMetadata['name']->hasAnnotation(TestAnnotation::class));

        $this->assertEquals(MetadataStub::class, $metadata->propertyMetadata['parent']->type);
        $this->assertEquals(MetadataStub::class, $metadata->propertyMetadata['child']->type);
        $this->assertEquals('string', $metadata->propertyMetadata['someValue']->type);
        $this->assertEquals(MetadataStub::class, $metadata->propertyMetadata['someOtherValue']->type);

        $this->assertTrue($metadata->propertyMetadata['createdAt']->isDateType());
        $this->assertTrue($metadata->propertyMetadata['createdAt']->isNativeType());
        $this->assertTrue($metadata->propertyMetadata['many']->isDecoratedType());
        $this->assertEquals('array', $metadata->propertyMetadata['many']->typeInfo['class']);
        $this->assertEquals(MetadataStub::class, $metadata->propertyMetadata['many']->typeInfo['decoration']);
        $this->assertEquals(MetadataStub::class, $metadata->methodMetadata['setSomeOtherValue']->params[0]->type);

        $this->assertCount(1, $metadata->getAnnotations());
        $this->assertInstanceOf(TestAnnotation::class, $metadata->getAnnotation(TestAnnotation::class));
        $this->assertEquals('int', $metadata->propertyMetadata['extra']->type);
        $this->assertEquals($name, $metadata->getAnnotation(TestAnnotation::class)->name);
        $this->assertTrue($metadata->propertyMetadata['overriden']->hasAnnotation(TestAnnotation::class));

        $this->assertTrue($metadata->propertyMetadata['someValue']->hasSetter());
        $this->assertTrue($metadata->propertyMetadata['id']->hasGetter());
        $this->assertInstanceOf(MethodMetadata::class, $metadata->propertyMetadata['someValue']->setter);
        $this->assertEquals('getId', $metadata->propertyMetadata['id']->getter->name);
        $this->assertEquals('setSomeValue', $metadata->propertyMetadata['someValue']->setter->name);
    }
}

class ParentStub {
    public int $extra;

    public $overriden;
}

/**
 * @TestAnnotation("some")
 */
class MetadataStub extends ParentStub {
    /**
     * @var int
     */
    public $id;

    /**
     * @TestAnnotation
     */
    public string $name;

    public MetadataStub $parent;

    /**
     * @var MetadataStub
     */
    public $child;

    public $someValue;

    public $someOtherValue;

    public \DateTime $createdAt;

    /**
     * @var array<MetadataStub>
     */
    public $many = [];

    /**
     * @TestAnnotation
     */
    public $overriden;

    public int | string $value;

    /**
     * @TestAnnotation
     */
    public function getId(): int {
        return $this->id;
    }

    public function setSomeValue(string $someValue): void
    {
        $this->someValue = $someValue;
    }

    public function setSomeOtherValue(MetadataStub $someOtherValue): void
    {
        $this->someOtherValue = $someOtherValue;
    }
}

class ParentStubYaml {
    public int $extra;

    public $overriden;
}

class MetadataStubYaml extends ParentStubYaml {
    /**
     * @var int
     */
    public $id;

    public string $name;

    public MetadataStub $parent;

    /**
     * @var MetadataStub
     */
    public $child;

    public $someValue;

    public $someOtherValue;

    public \DateTime $createdAt;

    /**
     * @var array<MetadataStub>
     */
    public $many = [];

    public $overriden;

    public function getId(): int {
        return $this->id;
    }

    public function setSomeValue(string $someValue): void
    {
        $this->someValue = $someValue;
    }

    public function setSomeOtherValue(MetadataStub $someOtherValue): void
    {
        $this->someOtherValue = $someOtherValue;
    }
}

#[TestAnnotation(name: 'my name')]
class AttributeStub extends ParentStub {
    /**
     * @var int
     */
    public $id;

    #[TestAnnotation]
    public string $name;

    public MetadataStub $parent;

    /**
     * @var MetadataStub
     */
    public $child;

    public $someValue;

    public $someOtherValue;

    public \DateTime $createdAt;

    /**
     * @var array<MetadataStub>
     */
    public $many = [];

    #[TestAnnotation]
    public $overriden;

    public $myValue;

    #[TestAnnotation]
    public function getId(): int {
        return $this->id;
    }

    public function setSomeValue(string $someValue): void
    {
        $this->someValue = $someValue;
    }

    public function setSomeOtherValue(MetadataStub $someOtherValue): void
    {
        $this->someOtherValue = $someOtherValue;
    }

    public function setMyValue(int | string $myValue)
    {
        $this->myValue = $myValue;
    }
}
