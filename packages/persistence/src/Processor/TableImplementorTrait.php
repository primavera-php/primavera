<?php

namespace Primavera\Persistence\Processor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Persistence\Annotation\Table;

trait TableImplementorTrait
{
    abstract public function getStereotypeName(): string;

    public function implementTable(ClassMetadataInterface $classMetadata, ClassGenerator $classGenerator)
    {
        $table = $classMetadata->getAnnotation(Table::class);
        $stereotype = $classMetadata->getAnnotation($this->getStereotypeName());

        $classGenerator->addMethods([
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->getName(), 'getTableName'))
                ->setBody("return '{$table->tableName}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->getName(), 'getIdColumnName'))
                ->setBody("return '{$table->idColunmName}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->getName(), 'getEntityClassname'))
                ->setBody("return '{$stereotype->entity}';")
                ->setAbstract(false)
                ->setInterface(false),
            MethodGenerator::copyMethodSignature(new MethodReflection($classMetadata->getName(), 'isAutoIncrementId'))
                ->setBody('return ' . ($table->autoIncrementId ? 'true;' : 'false;'))
                ->setAbstract(false)
                ->setInterface(false),
        ]);
    }
}