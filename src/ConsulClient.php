<?php


namespace YaangVu\Consul;


use SensioLabs\Consul\ServiceFactory;
use SensioLabs\Consul\Services\KVInterface;

class ConsulClient
{
    public static $client;

    /**
     * ConsulClient constructor.
     *
     * @param string $uri
     * @param string $token
     */
    public function __construct(string $uri = "", string $token = "")
    {
        if (!$uri)
            $uri = env('CONSUL_URI');
        if (!$token)
            $token = env('CONSUL_TOKEN');

        $this->connect($uri, $token);
    }

    private function connect(string $uri, string $token): void
    {
        $config = [
            "base_uri"   => $uri,
            "auth_basic" => "headers",
            "headers"    => [
                "X-Consul-Token" => $token
            ],
        ];

        $sf = new ServiceFactory($config);

        self::$client = $sf->get(KVInterface::class);
    }

    public static function get($key)
    {
        return self::$client->get($key);
    }
}
