<?php

namespace Primavera\Persistence\Processor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Primavera\Implementor\AbstractInterfaceImplementor;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;
use Primavera\Persistence\Repository\DbalBaseRepository;
use Primavera\Persistence\Stereotype\Repository;

class DbalRepositoryImplementor extends AbstractInterfaceImplementor
{
    use TableImplementorTrait;

    public function getStereotypeName(): string
    {
        return Repository::class;
    }

    protected function postProcess(ClassMetadataInterface $classMetadata, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass(DbalBaseRepository::class);
        $classGenerator->addProperty('interface', $classMetadata->getName(), PropertyGenerator::FLAG_PROTECTED);
        
        $this->implementTable($classMetadata, $classGenerator);
    }

    protected function getBlacklistedMethods(): array
    {
        return [
            'find',
            'findById',
            'findOne',
            'getTableName',
            'getIdColumnName',
            'getEntityClassname',
            'isAutoIncrementId',
            'save',
            'insert',
            'update',
        ];
    }

    public function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadataInterface $metadata,
                                        ClassMetadataInterface $classMetadata): string
    {
        return "return \$this->findByMethodExpressions('{$metadata->getName()}', get_defined_vars());";
    }
}