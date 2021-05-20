<?php


return [
    'uri'   => env('CONSUL_URI', '127.0.0.1:8500'),
    'token' => env('CONSUL_token', ''),
    'keys' => [
        // ... Define key to get value
    ]
];
