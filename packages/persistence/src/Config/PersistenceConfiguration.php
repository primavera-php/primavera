<?php

namespace Primavera\Persistence\Config;

use Primavera\Container\Annotation\Factory;
use Primavera\Persistence\Parser\ExpressionFactoryInterface;
use Primavera\Persistence\Parser\MethodNameToQueryParser;

class PersistenceConfiguration
{
    #[Factory]
    public function methodNameToQueryParser(ExpressionFactoryInterface $expressionFactory): MethodNameToQueryParser
    {
        return new MethodNameToQueryParser($expressionFactory);
    }
}