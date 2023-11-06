<?php

namespace Primavera\Framework\Component\Http;

interface HttpPromiseInterface {
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null
    );

    public function otherwise(callable $onRejected);

    public function getState();

    public function resolve($value);

    public function reject($reason);

    public function cancel();

    public function wait($unwrap = true);
}
