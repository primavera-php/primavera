<?php

namespace Primavera\Framework\Component\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

class HttpGuzzleBridge extends Client implements HttpHandlerInterface {
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface {
        return new HttpPromiseGuzzleBridge(parent::sendAsync($request, $options));
    }
}
