<?php

namespace Primavera\Framework\Component\Http;

interface HttpClientInterface {
    public function post(string $path, $body, array $headers = []);

    public function put(string $path, $body, array $headers = []);
    
    public function get(string $path, array $query = [], array $headers = []);
    
    public function delete(string $path, array $query = [], array $headers = []);
}
