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

        /* @var $table \Primavera\Persistence\Annotation\Table */
        $table = $classMetadata->getAnnotation(Table::class);
        /* @var $repository Repository */
        $repository = $classMetadata->getAnnotation(Repository::class);

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
                ->setBody("return '{$repository->entity}';")
                ->setAbstract(false)
                ->setInterface(false),
        ]);
    }

    protected function getBlacklistedMethods(): array
    {
        return ['find', 'findById', 'findOne', 'getTableName', 'getIdColumnName', 'getEntityClassname'];
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