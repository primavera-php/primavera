<?php

namespace Primavera\Persistence\BeanProcessor;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Container\Bean\AbstractInterfaceImplementor;
use Primavera\Container\Metadata\ClassMetadata;
use Primavera\Metadata\MethodMetadata;
use Primavera\Persistence\Annotation\Table;
use Primavera\Persistence\Parser\ParserInterface;
use Primavera\Persistence\Repository\DbalBaseRepository;
use Primavera\Persistence\Stereotype\Repository;

#[IgnoreScanner]
class DbalRepositoryImplementor extends AbstractInterfaceImplementor
{
    use TableImplementorTrait;

    private ParserInterface $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function getStereotypeName(): string
    {
        return Repository::class;
    }

    protected function postProcess(ClassMetadata $classMetadata, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass(DbalBaseRepository::class);
        
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

    public function implementMethodBody(MethodGenerator $methodGenerator, MethodMetadata $metadata,
                                        ClassMetadata $classMetadata): string
    {
        $exprs = $this->parser->parse($metadata);
        $operation = array_shift($exprs)['operation'];
        $exprs = var_export($exprs, true);

        return "return \$this->findByExpressions('{$operation}', {$exprs}, get_defined_vars());";
    }
}