<?php

namespace Vox\Persistence\Config;

use Primavera\Container\Annotation\Bean;
use Vox\Persistence\Parser\ExpressionFactoryInterface;
use Vox\Persistence\Parser\MethodNameToQueryParser;

class PersistenceConfiguration
{
    #[Bean]
    public function methodNameToQueryParser(ExpressionFactoryInterface $expressionFactory): MethodNameToQueryParser
    {
        return new MethodNameToQueryParser($expressionFactory);
    }
}