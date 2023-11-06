<?php


namespace Shared\Stub;

use Primavera\Container\Annotation\Autowired;
use Primavera\Container\Annotation\Component;
use Primavera\Container\Annotation\Injects;
use Primavera\Container\Annotation\Value;

#[Component]
class BazComponent
{
    /**
     * @Autowired
     *
     * @var FooComponent
     */
    private FooComponent $fooComponent;

    /**
     * @Value("someValue")
     *
     * @var string
     */
    public string $someValue = '';

    private SomeComponent $someComponent;

    public function __construct(
        #[Injects('some-component')]
        SomeComponent $someComponent
    ) {
        $this->someComponent = $someComponent;
    }

    /**
     * @return FooComponent
     */
    public function getFooComponent(): FooComponent
    {
        return $this->fooComponent;
    }

    public function getSomeComponent(): SomeComponent
    {
        return $this->someComponent;
    }
}