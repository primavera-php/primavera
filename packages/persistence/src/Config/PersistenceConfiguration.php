<?php

namespace Primavera\Persistence\Config;

use Primavera\Container\Annotation\Bean;
use Primavera\Persistence\Parser\ExpressionFactoryInterface;
use Primavera\Persistence\Parser\MethodNameToQueryParser;

class PersistenceConfiguration
{
    #[Bean]
    public function methodNameToQueryParser(ExpressionFactoryInterface $expressionFactory): MethodNameToQueryParser
    {
        return new MethodNameToQueryParser($expressionFactory);
    }
}