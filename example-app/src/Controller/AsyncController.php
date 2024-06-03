<?php

namespace Primavera\ExampleApp\Controller;

use Primavera\Framework\Stereotype\Controller;
use Primavera\Http\Stereotype\Get;

#[Controller('async')]
class AsyncController
{
    #[Get('tests')]
    public function tests()
    {
        
    }
}
