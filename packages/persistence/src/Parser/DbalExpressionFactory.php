<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\Token;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\ParamMetadata;

class DbalExpressionFactory implements ExpressionFactoryInterface
{
    private Connection $connection;

    private ExpressionBuilder $expressionBuilder;

    private ParserInterface $sqlParser;

    public function __construct(Connection $connection, ParserInterface $sqlParser)
    {
        $this->connection = $connection;
        $this->sqlParser = $sqlParser;
        $this->expressionBuilder = $connection->createExpressionBuilder();
    }


    public function createExpression(Token $token, string $operator, MethodMetadata $methodMetadata,
                                     ParamMetadata $paramMetadata)
    {
        return $this->expressionBuilder->$operator($token->value, ":{$paramMetadata->name}");
    }

    public function createAggregateExpression(Token $token, MethodMetadata $methodMetadata,
                                              ParamMetadata $paramMetadata)
    {
        return $token['value'];
    }

    public function createLimitExpression(Token $token, MethodMetadata $methodMetadata, ParamMetadata $paramMetadata)
    {
        return ":{$paramMetadata->name}";
    }

    public function parseSqlExpression(string $query): array
    {
        return $this->sqlParser->parse($query);
    }
}