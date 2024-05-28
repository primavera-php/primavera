<?php

namespace Primavera\Framework\Middleware;

use Psr\Http\Message\ServerRequestInterface;

trait GetAcceptsTrait
{
    private function getAcceptData(ServerRequestInterface $request): string | null
    {
        $accept = $request->getHeader('Accept');

        if ($accept == '*' || empty($accept)) {
            return null;
        }

        return explode(',', $accept)[0];
    }
}