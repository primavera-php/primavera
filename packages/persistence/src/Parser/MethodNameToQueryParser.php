<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\Token;
use Primavera\Container\Annotation\Component;
use Primavera\Container\Annotation\IgnoreScanner;
use Primavera\Metadata\MethodMetadata;
use Primavera\Persistence\Annotation\GroupBy;
use Primavera\Persistence\Annotation\Limit;
use Primavera\Persistence\Annotation\OrderBy;
use Primavera\Persistence\Annotation\Query;
use Primavera\Persistence\Parser\MethodNameToQueryLexer as Lexer;

#[Component]
#[IgnoreScanner]
class MethodNameToQueryParser implements ParserInterface
{
    private ExpressionFactoryInterface $expressionFactory;

    private const SYNTAX = [
        Lexer::T_OPERATION => [Lexer::T_FIELD, Lexer::T_LOGICAL],
        Lexer::T_FIELD => [
            Lexer::T_LOGICAL,
            Lexer::T_AGGREGATE,
            Lexer::T_OPERATOR,
            Lexer::T_FIELD,
            Lexer::T_LIMIT,
            Lexer::T_ORDER,
            Lexer::T_DIRECTION
        ],
        Lexer::T_LOGICAL => [Lexer::T_FIELD],
        Lexer::T_AGGREGATE => [Lexer::T_FIELD],
        Lexer::T_ORDER => [Lexer::T_FIELD],
        Lexer::T_OPERATOR => [Lexer::T_LOGICAL, Lexer::T_AGGREGATE, Lexer::T_LIMIT],
    ];

    private const SYNTAX_ROOT = Lexer::T_OPERATION;

    public function __construct(ExpressionFactoryInterface $expressionFactory)
    {
        $this->expressionFactory = $expressionFactory;
    }

    /**
     * @param MethodMetadata $context
     * @return array<string,string>
     */
    public function parse($context)
    {
        $lexer = Lexer::create($context->name);
        $lexer->moveNext();
        $paramWalker = ParamWalker::create($context);
        $result = new \ArrayObject();

        TokenWalker::create($lexer, self::SYNTAX, [self::SYNTAX_ROOT])
            ->on(self::SYNTAX_ROOT, function (Token $token, ...$params) use ($result) {
                $result[] = ['operation' => $token->value];
            })
            ->on(Lexer::T_LOGICAL, function (Token $token, Token $lastToken, TokenWalker $walker) use ($result, $paramWalker, $context) {
                $value = strtolower($token->value);

                if ($value === 'by') {
                    $value = 'and';
                }

                $token->value = implode('', $walker->getValues($walker->getWhileIsType(Lexer::T_FIELD)));
                $param = $paramWalker->next()->getParam();
                $operator = 'eq';

                if ($walker->checkNextType(Lexer::T_OPERATOR, false)) {
                    $operator = lcfirst($walker->getNext()->value);
                }

                $expr = $this->expressionFactory->createExpression($token, $operator, $context, $param);

                $result[] = [$value => $expr];
            })
            ->walk()
        ;

        $result = $result->getArrayCopy();

        if ($context->hasAnnotation(Query::class)) {
            $query = $context->getAnnotation(Query::class)->query;

            $result = array_merge($result, $this->expressionFactory->parseSqlExpression($query));
        }

        if ($context->hasAnnotation(OrderBy::class)) {
            $group = $context->getAnnotation(OrderBy::class)->fields;
            $data = [];

            foreach ($group as $field) {
                $data = preg_split('/\s+/', trim($field), 2);

                if (count($data) === 1) {
                    $data[] = 'asc';
                }
            }

            $result[] = ['orderby' => [$data[0] => $data[1]]];
        }

        if ($context->hasAnnotation(GroupBy::class)) {
            $order = $context->getAnnotation(GroupBy::class)->fields;

            $result[] = ['groupby' => $order];
        }

        if ($context->hasAnnotation(Limit::class)) {
            $limit = $context->getAnnotation(Limit::class)->limit;

            $result[] = ['limit' => $limit];
        }

        return $result;
    }
}