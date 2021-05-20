<?php


return [
    /**
     * Consul URI
     */
    'uri'    => env('CONSUL_URI', '127.0.0.1:8500'),

    /**
     * Consul Token
     */
    'token'  => env('CONSUL_token', ''),

    /**
     * Consul keys list
     */
    'keys'   => [
        // 'foo', 'bar'
    ],

    /**
     * Consul scheme
     */
    'scheme' => env('CONSUL_SCHEME', 'http'),

    /**
     * Consul datacenter
     */
    'dc'     => env('CONSUL_DC', 'dc1')
];
