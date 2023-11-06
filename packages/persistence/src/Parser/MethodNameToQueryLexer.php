<?php

namespace Primavera\Persistence\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Primavera\Metadata\MethodMetadata;

class MethodNameToQueryLexer extends AbstractLexer
{
    const T_OPERATION = 1;
    const T_FIELD = 2;
    const T_LOGICAL = 3;
    const T_AGGREGATE = 4;
    const T_OPERATOR = 5;
    const T_LIMIT = 6;
    const T_ORDER = 7;
    const T_DIRECTION = 8;

    public static function create(string $input)
    {
        $lexer = new self();
        $lexer->setInput($input);

        return $lexer;
    }

    protected function getModifiers()
    {
        return 'u';
    }

    protected function getCatchablePatterns()
    {
        return [
            'findOne',
            'find',
            '[\S]+?(?=[A-Z])',
            '[\S]+$',
        ];
    }

    protected function getNonCatchablePatterns()
    {
        return [];
    }

    protected function getType(&$value)
    {
        switch ($value) {
            case 'find':
            case 'findOne':
                return self::T_OPERATION;
            case 'And':
            case 'Or':
            case 'By':
                return self::T_LOGICAL;
            case 'Gt':
            case 'Gte':
            case 'In':
            case 'NotIn':
            case 'Lt':
            case 'Lte':
            case 'Neq':
            case 'Like':
            case 'NotLike':
                return self::T_OPERATOR;
            default:
                return self::T_FIELD;
        }
    }
}