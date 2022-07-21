<?php

return [
    /**
     * Consul URI
     */
    'uri'       => env('CONSUL_URI', '127.0.0.1:8500'),

    /**
     * Consul Token
     */
    'token'     => env('CONSUL_TOKEN', ''),

    /**
     * Consul keys list
     */
    'keys'      => [
        env('CONSUL_PATH')
    ],

    /**
     * Consul scheme
     */
    'scheme'    => env('CONSUL_SCHEME', 'http'),

    /**
     * Consul datacenter
     */
    'dc'        => env('CONSUL_DC', 'dc1'),

    /**
     * Consul recursive
     */
    'recursive' => env('CONSUL_RECURSIVE', false)
];
