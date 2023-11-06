<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\AbstractLexer;

class SimpleSqlLexer extends AbstractLexer
{
    const T_OPERATION = 'operation';
    const T_FROM = 'from';
    const T_WHERE = 'where';
    const T_OPERATOR = 'operator';
    const T_NAME = 'name';
    const T_VARIABLE = 'variable';
    const T_AGGREGATE = 'aggregate';
    const T_LIMIT = 'limit';
    const T_ORDER = 'order';
    const T_DIRECTION = 'direction';
    const T_SET = 'set';

    public static function create($input): self
    {
        $lexer = new self();
        $lexer->setInput($input);

        return $lexer;
    }

    protected function getCatchablePatterns()
    {
        return [
            'not\s+in',
            'is\s+not',
            'not\s+like',
            'group\s+by',
            'order\s+by',
            '[a-zA-Z0-9:_]+',
            '\>\=|\<\=|\=|\!\=|\<\>',
        ];
    }

    protected function getType(&$value)
    {
        switch (strtolower($value)) {
            case 'select':
            case 'insert':
            case 'update':
            case 'delete':
                return self::T_OPERATION;
            case 'from':
                return self::T_FROM;
            case 'where':
            case 'and':
            case 'or':
                return self::T_WHERE;
            case '>=':
            case '<=':
            case '=':
            case '!=':
            case '<>':
            case 'is':
            case 'is not':
            case 'like':
            case 'not like':
            case 'in':
            case 'not in':
                return self::T_OPERATOR;
            case 'group by':
                return self::T_AGGREGATE;
            case 'limit':
                return self::T_LIMIT;
            case 'order by':
                return self::T_ORDER;
            case 'asc':
            case 'desc':
                return self::T_DIRECTION;
            case 'set':
                return self::T_SET;
        }

        if (str_starts_with($value, ':')) {
            return self::T_VARIABLE;
        }

        return self::T_NAME;
    }

    protected function getNonCatchablePatterns()
    {
        return ['\(', '\)', '\,', '', '\s'];
    }

//    protected function getModifiers()
//    {
//        return 'u';
//    }
}