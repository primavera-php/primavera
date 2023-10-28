<?php

namespace Vox\Persistence\Config;

use PhpBeans\Annotation\Bean;
use Vox\Persistence\Parser\ExpressionFactoryInterface;
use Vox\Persistence\Parser\MethodNameToQueryParser;
use Vox\Persistence\Parser\ParserInterface;

class PersistenceConfiguration
{
    /**
     * @Bean
     */
    #[Bean]
    public function methodNameToQueryParser(ExpressionFactoryInterface $expressionFactory): ParserInterface
    {
        return new MethodNameToQueryParser($expressionFactory);
    }
}