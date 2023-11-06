<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\Token;
use Doctrine\DBAL\Connection;
use Primavera\Persistence\Parser\SimpleSqlLexer as Lexer;

class SimpleSqlParser implements ParserInterface
{
    const SYNTAX = [
        Lexer::T_OPERATION => [Lexer::T_NAME],
        Lexer::T_NAME => [
            Lexer::T_NAME,
            Lexer::T_FROM,
            Lexer::T_AGGREGATE,
            Lexer::T_LIMIT,
            Lexer::T_WHERE,
            Lexer::T_OPERATOR,
            Lexer::T_ORDER,
            Lexer::T_DIRECTION,
            Lexer::T_SET,
        ],
        Lexer::T_SET => [Lexer::T_NAME],
        Lexer::T_VARIABLE => [
            Lexer::T_WHERE,
            Lexer::T_NAME,
            Lexer::T_AGGREGATE,
            Lexer::T_LIMIT,
            Lexer::T_ORDER,
        ],
        Lexer::T_FROM => [Lexer::T_NAME],
        Lexer::T_WHERE => [Lexer::T_NAME],
        Lexer::T_OPERATOR => [Lexer::T_VARIABLE, Lexer::T_NAME],
        Lexer::T_AGGREGATE => [Lexer::T_NAME],
        Lexer::T_LIMIT => [Lexer::T_VARIABLE],
    ];

    const SYNTAX_ROOT = [
        Lexer::T_OPERATION,
        Lexer::T_WHERE,
    ];

    const OPERATOR_MAP = [
        '=' => 'eq',
        'is' => 'eq',
        '!=' => 'neq',
        'is not' => 'neq',
        '<>' => 'neq',
        '>' => 'gt',
        '>=' => 'gte',
        '<' => 'lt',
        '<=' => 'lte',
        'in' => 'in',
        'not in' => 'notIn',
        'like' => 'like',
        'not like' => 'notLike',
    ];

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function parse($context)
    {
        $result = new \ArrayObject();
        $exprBuilder = $this->connection->createExpressionBuilder();

        TokenWalker::create(Lexer::create($context), self::SYNTAX, self::SYNTAX_ROOT)
            ->on(Lexer::T_OPERATION, function (Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $value = strtolower($token->value);

                if ($value == 'select') {
                    $fields = $walker->getValues($walker->getWhileIsType(Lexer::T_NAME));

                    $result[] = ['select' => $fields];
                } elseif (in_array($value, ['insert', 'update', 'delete'])) {
                    $result[] = [$value => $walker->getNextIfType(Lexer::T_NAME)->value];
                }
            })
            ->on(Lexer::T_FROM, function (Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $result[] = ['from' => $walker->getNextIfType(Lexer::T_NAME)->value];
            })
            ->on(Lexer::T_WHERE, function (Token $token, ?Token $lastToken, TokenWalker $walker) use ($result, $exprBuilder) {
//                if (!$lastToken) {
//                    $result[] = ['select' => '*'];
//                }

                $logical = strtolower($token->value) == 'where' ? 'and' : strtolower($token->value);
                $exp = $walker->getValues($walker->getSequence(Lexer::T_NAME, Lexer::T_OPERATOR, [Lexer::T_NAME, Lexer::T_VARIABLE]));

                $result[] = [$logical => $exprBuilder->{self::OPERATOR_MAP[strtolower($exp[1])]}($exp[0], $exp[2])];
            })
            ->on(Lexer::T_AGGREGATE, function (Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $agr = strtolower(str_replace(' ', '', $token->value));

                $result[] = [$agr => $walker->getValues($walker->getWhileIsType(Lexer::T_NAME))];
            })
            ->on(Lexer::T_LIMIT, function (Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $result[] = ['limit' => $walker->getNextIfType([Lexer::T_NAME, Lexer::T_VARIABLE])->value];
            })
            ->on(Lexer::T_ORDER, function(Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $lexer = $walker->getLexer();
                $logical = 'orderby';

                $fields = [];

                while ($lexer->lookahead && $lexer->lookahead->type === Lexer::T_NAME) {
                    $lexer->moveNext();

                    $field = $lexer->token->value;
                    $direction = 'asc';

                    if ($lexer->lookahead && $lexer->lookahead->type === Lexer::T_DIRECTION) {
                        $lexer->moveNext();
                        $direction = strtolower($lexer->token->value);
                    }

                    $fields[$field] = $direction;
                }

                $result[] = [$logical => $fields];
            })
            ->on(Lexer::T_SET, function(Token $token, ?Token $lastToken, TokenWalker $walker) use ($result) {
                $logical = 'set';

                $seqs = $walker->getWhileSequence(Lexer::T_NAME, Lexer::T_OPERATOR, [Lexer::T_VARIABLE, Lexer::T_NAME]);
                $exprs = iterator_map($seqs, fn($seq) => implode(' ', $walker->getValues($seq)));

                $result[] = [$logical => $exprs];
            })
            ->walk()
        ;

        return $result->getArrayCopy();
    }
}
