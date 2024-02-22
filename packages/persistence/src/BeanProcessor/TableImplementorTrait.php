<?php

namespace Primavera\Persistence\BeanProcessor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Persistence\Annotation\Table;

trait TableImplementorTrait
{
    abstract public function getStereotypeName(): string;

    public function implementTable(ClassMetadata $classMetadata, ClassGenerator $classGenerator)
    {
        $table = $classMetadata->getAnnotation(Table::class);
        $stereotype = $classMetadata->getAnnotation($this->getStereotypeName());

        $classGenerator->addMethods([
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->name, 'getTableName'))
                ->setBody("return '{$table->tableName}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->name, 'getIdColumnName'))
                ->setBody("return '{$table->idColunmName}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->name, 'getEntityClassname'))
                ->setBody("return '{$stereotype->entity}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->name, 'isAutoIncrementId'))
                ->setBody('return ' . ($table->autoIncrementId ? 'true;' : 'false;'))
                ->setAbstract(false)
                ->setInterface(false),
        ]);
    }
}