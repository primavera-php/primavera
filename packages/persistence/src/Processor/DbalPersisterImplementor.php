<?php

namespace Primavera\Persistence\Processor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Primavera\Implementor\AbstractInterfaceImplementor;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\MethodMetadataInterface;
use Primavera\Persistence\Parser\ParserInterface;
use Primavera\Persistence\Persister\DbalBasePersister;
use Primavera\Persistence\Stereotype\Persister;

class DbalPersisterImplementor extends AbstractInterfaceImplementor
{
    use TableImplementorTrait;

    public function getStereotypeName(): string
    {
        return Persister::class;
    }

    protected function postProcess(ClassMetadataInterface $classMetadata, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass(DbalBasePersister::class);

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
            'delete',
        ];
    }

    public function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadataInterface $metadata,
                                        ClassMetadataInterface $classMetadata): string
    {
        return "return \$this->findByMethodExpressions('{$metadata->getName()}', get_defined_vars());";
    }
}