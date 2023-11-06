<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\Token;
use Primavera\Metadata\MethodMetadata;
use Primavera\Metadata\ParamMetadata;

interface ExpressionFactoryInterface
{
    public function createExpression(Token $token,  string $operator, MethodMetadata $methodMetadata,
                                     ParamMetadata $paramMetadata);

    public function createAggregateExpression(Token $token, MethodMetadata $methodMetadata,
                                              ParamMetadata $paramMetadata);

    public function createLimitExpression(Token $token, MethodMetadata $methodMetadata, ParamMetadata $paramMetadata);

    public function parseSqlExpression(string $query): array;
}