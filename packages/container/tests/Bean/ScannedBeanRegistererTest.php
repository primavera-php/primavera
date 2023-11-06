<?php


namespace ScannedTest\Bean;


use example\shared\Stub\IgnoredClass;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Container\Cache\ContainerCacheGenerator;
use Primavera\Container\Container\NotFoundContainerException;
use Primavera\Container\Factory\ContainerBuilder;
use Shared\Annotation\TestImport;
use Shared\Stub\BarComponent;
use Shared\Stub\BazComponent;
use Shared\Stub\BeanComponent;
use Shared\Stub\FooComponent;
use Shared\Stub\TestImportService;
use PHPUnit\Framework\TestCase;
use Primavera\Cache\Factory;

class ScannedBeanRegistererTest extends TestCase
{
    /**
     * @dataProvider provider
     */
   public function testShouldRegisterBeans($withCache) {
        $builder = new ContainerBuilder();

        $builder->withAppNamespaces()
            ->withNamespaces('ScannedTest\\')
            ->withNamespaces('Shared\\')
            ->withBeans(['someValue' => 'lorem ipsum'])
            ->withStereotypes(TestImport::class)
            ->withConfigFile(__DIR__ . '/../example/shared/configs/application.yaml')
        ;

        if ($withCache) {
            $builder->withCache(
                (new Factory)
                    ->createSimpleCache(Factory::PROVIDER_SYMFONY, Factory::TYPE_FILE, '', 0, 'build/cache')
            );
        }

        $container = $builder->build();

        /* @var $foo \ScannedTest\Stub\FooComponent */
        $foo = $container->get(FooComponent::class);

        $this->assertInstanceOf(FooComponent::class, $foo);
        $this->assertEquals('lorem ipsum', $foo->getSomeValue());

        /* @var $bar \ScannedTest\Stub\BarComponent */
        $bar = $container->get(BarComponent::class);

        $this->assertEquals('lorem ipsum', $bar->getFooComponent()->getSomeValue());

        $this->assertEquals('default', $bar->getDefaultValue());
        $this->assertEquals(10, $bar->getValue());

        $this->assertEquals(
            'lorem ipsum',
            $container->get(BeanComponent::class)->getBarComponent()->getFooComponent()->getSomeValue()
        );

        $this->assertEquals(
            'lorem ipsum',
            $container->get(BazComponent::class)->someValue
        );

        $this->assertEquals(
            'some name',
            $container->get(BazComponent::class)->getSomeComponent()->getSomeName()
        );

        $this->assertInstanceOf(FooComponent::class, $container->get(BazComponent::class)->getFooComponent());
        $this->assertInstanceOf(TestImportService::class, $container->get(TestImportService::class));
        $this->assertEquals("lorem ipsum", $container->get(TestImportService::class)->value);
    }

    public function testScannerShouldIgnoreIgnorableComponents()
    {
        $builder = new ContainerBuilder();

        $builder->withAppNamespaces()
            ->withNamespaces('ScannedTest\\')
            ->withNamespaces('Shared\\')
            ->withBeans(['someValue' => 'lorem ipsum'])
            ->withConfigFile(__DIR__ . '/../example/shared/configs/application.yaml')
        ;

        $container = $builder->build();

        $this->expectException(NotFoundContainerException::class);

        $container->get(IgnoredClass::class);
    }

    public function provider() {
        return [
            [false],
            [true],
            [true],
        ];
    }
}