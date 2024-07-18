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

    private ParserInterface $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

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
        $exprs = $this->parser->parse($metadata);
        $operation = array_shift($exprs)['operation'];
        $exprs = var_export($exprs, true);

        return "return \$this->findByExpressions('{$operation}', {$exprs}, get_defined_vars());";
    }
}