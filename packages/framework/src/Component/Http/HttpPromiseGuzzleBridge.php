<?php

namespace Primavera\Framework\Component\Http;

use GuzzleHttp\Promise\PromiseInterface;

class HttpPromiseGuzzleBridge implements HttpPromiseInterface {
    private PromiseInterface $promise;
    
    public function cancel() {
        return $this->promise->cancel();
    }

    public function getState() {
        return $this->promise->getState();
    }

    public function otherwise(callable $onRejected) {
        return $this->promise->otherwise($onRejected);
    }

    public function reject($reason) {
        return $this->promise->reject($reason);
    }

    public function resolve($value) {
        return $this->promise->resolve($value);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null) {
        return $this->promise->then($onFulfilled, $onRejected);
    }

    public function wait($unwrap = true) {
        return $this->promise->wait($unwrap);
    }
}
