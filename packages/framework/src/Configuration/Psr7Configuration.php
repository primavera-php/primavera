<?php

namespace Primavera\Framework\Configuration;

use Primavera\Container\Annotation\Bean;
use Primavera\Container\Annotation\Configuration;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

#[Configuration]
class Psr7Configuration
{
    #[Bean]
    public function app(): App
    {
        return AppFactory::create();
    }


    #[Bean]
    public function responseFactory(): ResponseFactory
    {
        return new ResponseFactory();
    }

    #[Bean]
    public function requestFactory(): RequestFactory
    {
        return new RequestFactory();
    }

    #[Bean]
    public function streamFactory(): StreamFactory
    {
        return new StreamFactory();
    }
}
