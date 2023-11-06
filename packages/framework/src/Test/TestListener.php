<?php


namespace Primavera\Framework\Test;


use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Metadata\ClassMetadata;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Prophecy\Prophet;
use Throwable;
use Primavera\Framework\Application;
use Primavera\Framework\Test\Stereotype\Mock;
use Primavera\Metadata\Factory\MetadataFactory;
use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Metadata\MethodMetadata;
use Primavera\Container\Metadata\ParamMetadata;

class TestListener implements \PHPUnit\Framework\TestListener
{
    /**
     * @var ?MetadataFactory<ClassMetadata<PropertyMetadata, MethodMetadata<ParamMetadata>>>
     */
    private ?MetadataFactory $metadataFactory = null;
    private ?Prophet $prophet = null;
    private ?Application $application = null;

    public function addError(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addError() method.
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        // TODO: Implement addWarning() method.
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        // TODO: Implement addFailure() method.
    }

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addIncompleteTest() method.
    }

    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addRiskyTest() method.
    }

    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        // TODO: Implement addSkippedTest() method.
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->metadataFactory = (new MetadataFactoryFactory)
            ->createAnnotationMetadataFactory(ClassMetadata::class);
        $this->prophet = new Prophet();
    }

    public function endTestSuite(TestSuite $suite): void
    {
        // TODO: Implement endTestSuite() method.
    }

    public function startTest(Test $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $test->setProphet($this->prophet);

        /* @var $metadata ClassMetadata */
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($test));
        $app = new Application();

        $test->setupApplication($app);

        $app->configure([$test, 'configureBuilder']);
        $mocks = [];
        $beans = ['debug' => true];

        foreach ($metadata->getAnnotatedProperties(Mock::class) as $propertyMetadata) {
            $annotation = $propertyMetadata->getAnnotation(Mock::class);
            $type = $annotation->type;
            $serviceId = $annotation->serviceId ?? $type;
            $mocks[$serviceId] = $mock = $this->prophet->prophesize($type);
            $beans[$serviceId] = $mock->reveal();
            $propertyMetadata->setValue($test, $mock);
        }

        $app->getBuilder()->withBeans($beans);

        $test->setApplication($app);

        foreach ($metadata->getAnnotatedProperties(Autowired::class) as $propertyMetadata) {
            $autowired = $propertyMetadata->getAnnotation(Autowired::class);
            $id = $autowired->beanId ?? $propertyMetadata->type;
            $propertyMetadata->setValue($test, $app->getContainer()->get($id));
        }
    }

    public function endTest(Test $test, float $time): void
    {
        // TODO: Implement endTest() method.
    }
}