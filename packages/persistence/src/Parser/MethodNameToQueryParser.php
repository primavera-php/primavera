<?php

namespace Vox\Persistence\Parser;

use Doctrine\Common\Lexer\Token;
use PhpBeans\Annotation\Component;
use PhpBeans\Annotation\IgnoreScanner;
use Vox\Metadata\MethodMetadata;
use Vox\Persistence\Annotation\GroupBy;
use Vox\Persistence\Annotation\Limit;
use Vox\Persistence\Annotation\OrderBy;
use Vox\Persistence\Annotation\Query;
use Vox\Persistence\Parser\MethodNameToQueryLexer as Lexer;

/**
 * @Component
 * @IgnoreScanner
 */
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


//        while($lexer->lookahead) {
//            $lexer->moveNext();
//            $token = $lexer->token;
//
//            if (!$lastToken && $token['type'] !== self::SYNTAX_ROOT) {
//                throw new \CompileError("invalid start operator {$token['value']}");
//            }
//
//            if ($lastToken && !in_array($token['type'], self::SYNTAX[$lastToken['type']])) {
//                throw new \CompileError("{$token['value']} cannot be used after {$lastToken['value']}");
//            }
//
//            if ($token['type'] === self::SYNTAX_ROOT) {
//                $exprs[] = ['operation' => $token['value']];
//            }
//
//            if ($token['type'] == Lexer::T_FIELD) {
//                $paramWalker->next();
//
//                $logical = $lastToken && $lastToken['type'] === Lexer::T_LOGICAL
//                    ? strtolower($lastToken['value'])
//                    : 'and';
//
//                $operator = 'eq';
//                $nextToken = $lexer->lookahead;
//
//                if ($nextToken && $nextToken['type'] === Lexer::T_OPERATOR) {
//                    $operator = lcfirst($nextToken['value']);
//                }
//
//                if ($lastToken && $lastToken['type'] === Lexer::T_FIELD) {
//                    array_pop($exprs);
//                    $token['value'] = "{$lastToken['value']}{$token['value']}";
//                    $paramWalker->previous();
//                }
//
//                $exp = $this->expressionFactory
//                    ->createExpression($token, $operator, $context, $paramWalker->getParam());
//
//                if ($lastToken && $lastToken['type'] === Lexer::T_AGGREGATE) {
//                    $logical = strtolower($lastToken['value']);
//                    $exp = $this->expressionFactory
//                        ->createAggregateExpression($token, $context, $paramWalker->getParam());
//                    $paramWalker->previous();
//                }
//
//                $exprs[] = [$logical => $exp];
//            }
//
//            if ($token['type'] === Lexer::T_LIMIT) {
//                $paramWalker->next();
//
//                $exprs[] = [
//                    strtolower($token['value']) => $this->expressionFactory
//                        ->createLimitExpression($token, $context, $paramWalker->getParam())
//                ];
//            }
//
//            // possible bug here, when camelcase fields are interpreted as 2 fields by the lexer
//            if ($token->type === Lexer::T_ORDER) {
//                $logical = 'orderby';
//
//                $lexer->moveNext();
//                $fields = [];
//
//                while ($lexer->lookahead->type === Lexer::T_FIELD) {
//                    $lexer->moveNext();
//
//                    $field = $lexer->token->value;
//                    $direction = 'asc';
//
//                    if ($lexer->lookahead->type === Lexer::T_DIRECTION) {
//                        $lexer->moveNext();
//                        $direction = strtolower($lexer->token->value);
//                    }
//
//                    $fields[$field] = $direction;
//                }
//
//                $exprs = [$logical => $fields];
//            }
//
//            $lastToken = $token;
//        }

        return $result;
    }
}