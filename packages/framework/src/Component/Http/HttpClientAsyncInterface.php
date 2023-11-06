<?php

namespace Primavera\Framework\Component\Http;

interface HttpClientAsyncInterface {
    public function postAsync(string $path, $body, array $headers = []);

    public function putAsync(string $path, $body, array $headers = []);
    
    public function getAsync(string $path, array $query = [], array $headers = []);
    
    public function deleteAsync(string $path, array $query = [], array $headers = []);
}
