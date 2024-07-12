<?php

namespace Primavera\Implementor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use Primavera\Container\ContainerBuilderInterface;
use Primavera\Container\Processor\ComponentPreProcessorInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;

abstract class AbstractInterfaceImplementor implements ComponentPreProcessorInterface
{
    public function __construct(
        public string $cacheDir = 'build/generated',
    ) {}

    abstract protected function getStereotypeName(): string;

    abstract protected function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadataInterface $metadata,
                                                 ClassMetadataInterface $classMetadata): string;

    protected function getBlacklistedMethods(): array
    {
        return [];
    }

    protected function createClassGenerator(ClassMetadataInterface $metadata): ClassGenerator
    {
        $methods = [];

        foreach ($metadata->getMethodMetadata() as $methodMetadata) {
            if (!$methodMetadata->getReflection()->isAbstract()
                || $methodMetadata->getReflection()->isProtected()
                || $methodMetadata->getReflection()->isPrivate()
                || in_array($methodMetadata->getName(), $this->getBlacklistedMethods())) {
                continue;
            }

            $methods[] = $method = MethodGenerator::copyMethodSignature(
                new MethodReflection($methodMetadata->getClass(), $methodMetadata->getName())
            );

            $method->setAbstract(false)
                ->setInterface(false);

            $method->setBody($this->implementMethodBody($method, $methodMetadata, $metadata));
        }

        $parentClass = $metadata->getReflection()->getParentClass();

        return new ClassGenerator(
            $metadata->getName() . 'Impl',
            $metadata->getReflection()->getNamespaceName(),
            null,
            $parentClass ? $parentClass->name : null,
            $metadata->getReflection()->isInterface() ? [$metadata->getName()] : [],
            [],
            $methods,
        );
    }

    protected function postProcess(ClassMetadataInterface $classMetadata, ClassGenerator $classGenerator)
    {
        // override this method to do desired post-processing like, adding properties, constructors or autowired stuff
    }

    protected function implementsClass(ClassMetadataInterface $classMetadata)
    {
        $className = "{$classMetadata->getName()}Impl";
        $filename = "{$this->cacheDir}/" . str_replace('\\', '_', $className) . ".php";

        if (!class_exists($className) && file_exists($filename) && $classMetadata->isFresh()) {
            require_once $filename;
        } elseif (!class_exists($className) && !file_exists($filename) || !$classMetadata->isFresh()) {
            $generator = $this->createClassGenerator($classMetadata);
            $this->postProcess($classMetadata, $generator);

            if ($this->cacheDir) {
                if (!is_dir($this->cacheDir)) {
                    mkdir($this->cacheDir, 0755, true);
                }

                file_put_contents($filename, "<?php \n" . $generator->generate());

                require_once $filename;
            } else {
                eval($generator->generate());
            }
        }

        return $className;
    }

    public function accept(ClassMetadataInterface $classMetadata)
    {
        return $classMetadata->hasAnnotation($this->getStereotypeName())
            || $classMetadata->instanceOf($this->getStereotypeName());
    }

    public function process(ClassMetadataInterface $component, ContainerBuilderInterface $containerBuilder)
    {
        $containerBuilder->withComponents($this->implementsClass($component));
    }

    public function canProcess(object $component): bool
    {
        return $component->getReflection()->isInterface() && $this->accept($component);
    }
}