<?php

namespace Primavera\Framework\Component\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpHandlerInterface {
    public function send(RequestInterface $request, array $options = []): ResponseInterface;
    public function sendAsync(RequestInterface $request, array $options = []);
}
