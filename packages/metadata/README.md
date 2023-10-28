# PHP Metadata Reader

Easily read php classes metadata using doctrine annotations, php 8 attributes or yaml files.
All metadata can be cached using any psr cache solution or doctrine cache, this library is also
capable of reading data from proxies created by ocramius proxy manager.

Reads types from typehints (also from php 7.4 properties), docblocks (using @var), return types (typehints and dockblocks) from:

* Classes
* Child classes
* Properties
* Methods
* Method Properties

This lib is also circular dependency safe.

## Usage
To read the metadata you need a MetadataFactory instance, you can create it manualy or using the convenient factory methods 

### Create a metadata factory instance
there are two metadata factory factory methods, one for annotations based and another yaml based

#### Create annotations metadatada factory:
```php
$factory = (new MetadataFactoryFactory(debug: false))->createAnnotationMetadataFactory();
```

the constructor may receive one optional parameter, to set debug on or off

#### Create yaml metadata factory:
```php
$factory = (new MetadataFactoryFactory(debug: true))->createYmlMetadataFactory(__DIR__ . '/../metadata');
```

#### Factory method interface
There are several options on these factory methods, read its documentation to understand them
```php
/**
 * @param string $metadataClassName the fqcn to be used as class metadata holder, must implement the interface
 * @param Reader|null $reader the desired annotation reader, a custom one can be derived from the doctrine interface
 * @param string $methodMetadataClassName the fqcn to be used as method metadata holder, must implement the interface
 * @param string $propertyMetadataClassName the fqcn to be used as property metadata holder, must implement the interface
 *
 * @return MetadataFactory
 */
public function createAnnotationMetadataFactory(
    string $metadataClassName = ClassMetadata::class,
    Reader $reader = null,
    string $methodMetadataClassName = MethodMetadata::class,
    string $propertyMetadataClassName = PropertyMetadata::class
);

/**
 * @param string $metadataPath the path for the folder containing the metadata yaml files
 * @param string $metadataClassName the fqcn to be used as class metadata holder, must implement the interface
 * @param string $methodMetadataClassName the fqcn to be used as method metadata holder, must implement the interface
 * @param string $propertyMetadataClassName the fqcn to be used as property metadata holder, must implement the interface
 * @param string $yamlExtension the desired extension for the yaml files
 *
 * @return MetadataFactory
 */
public function createYmlMetadataFactory(
    string $metadataPath,
    string $metadataClassName = ClassMetadata::class,
    string $methodMetadataClassName = MethodMetadata::class,
    string $propertyMetadataClassName = PropertyMetadata::class,
    string $yamlExtension = 'yaml'
);
```

#### Adding cache for optimization

This library uses jms/metadata implementation to store its metadata, that means that the metadata is fully serializable 
and cacheable you can create your own cache implementation of Metadata\Cache\CacheInterface however, this package bundles
a psr-16 simple cache adapter, see the example:

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Vox\Metadata\Cache\PsrSimpleCacheAdapter;

$factory = (new MetadataFactoryFactory(true))->createAnnotationMetadataFactory();

$factory->setCache(new PsrSimpleCacheAdapter(new Psr16Cache(new FileSystemAdapter())));
```

#### Creating your own annotations
There are 2 types of annotations you can use, doctrine's or php 8 attributes, look at an example compatible with both in
case you want to suport php 7.4 and 8

```php
/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class TestAnnotation
{
    /**
     * @var string
     */
    public string $name = 'default';

    public function __construct($name = 'default')
    {
        $this->name = $name;
    }
}
```

#### Reading the metadata

Annotating the class:

```php
/**
 * @Controller() 
 */
class SomeAnnotatedClass {
    private SomeService $service;
    
    /**
     * @var SomeType 
     */
    private $someType;
    
    #[Get('/{id}')]
    public function getData(int $id) {}
}
```

Reading the class

```php
$factory = (new MetadataFactoryFactory(true))->createAnnotationMetadataFactory();

$metadata = $factory->getMetadataForClass(SomeAnnotatedClass::class);

$metadata->name; // MyNamespace\\SomeAnnotatedClass
$metadata->getAnnotations(); // [ MyNamespace\\Controller ]
$metadata->getAnnotation(Controller::class); // MyNamespace\\Controller
$metadata->propertyMetadata['service']->type; // MyNamespace\\SomeService
$metadata->propertyMetadata['someType']->type; // MyNamespace\\SomeType
$metadata->methodMetadata['getData']->getAnnotations(); // [ MyNamespace\\Get ]
$metadata->methodMetadata['getData']->params[0]->type; // int
```