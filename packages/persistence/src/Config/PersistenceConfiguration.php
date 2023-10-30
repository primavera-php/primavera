<?php

namespace Vox\Persistence\Config;

use PhpBeans\Annotation\Bean;
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