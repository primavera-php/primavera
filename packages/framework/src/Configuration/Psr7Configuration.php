<?php

namespace Primavera\Framework\Configuration;

use Primavera\Container\Annotation\Factory;
use Primavera\Container\Annotation\Configuration;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

#[Configuration]
class Psr7Configuration
{
    #[Factory]
    public function app(): App
    {
        return AppFactory::create();
    }


    #[Factory]
    public function responseFactory(): ResponseFactory
    {
        return new ResponseFactory();
    }

    #[Factory]
    public function requestFactory(): RequestFactory
    {
        return new RequestFactory();
    }

    #[Factory]
    public function streamFactory(): StreamFactory
    {
        return new StreamFactory();
    }
}
