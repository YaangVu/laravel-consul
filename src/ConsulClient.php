<?php


namespace YaangVu\Consul;


use DCarbone\PHPConsulAPI\Config;
use DCarbone\PHPConsulAPI\Consul;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ConsulClient
{
    public static Consul $consul;

    /**
     * ConsulClient constructor.
     *
     * @param string $uri
     * @param string $token
     * @param string $scheme
     * @param string $dc
     */
    public function __construct(string $uri, string $token, string $scheme, string $dc)
    {
        $this->connect($uri, $token, $scheme, $dc);
    }

    /**
     * @param string $key
     *
     * @return string
     * @throws GuzzleException
     */
    public static function get(string $key): string
    {
        return self::$consul->KV->Get($key)->getValue()->Value;
    }

    public static function set(string $key, string $value)
    {
        // Do later
    }

    /**
     * @param string $uri
     * @param string $token
     * @param string $scheme
     * @param string $dc
     *
     * @return $this
     */
    public function connect(string $uri, string $token, string $scheme = 'http', string $dc = 'dc1'): ConsulClient
    {
        $config = new Config(
            [
                'HttpClient' => new Client(),
                'Address'    => $uri,
                'Scheme'     => $scheme,
                'Datacenter' => env('CONSUL_DC', 'dc1'),
                'Token'      => $token,
                'WaitTime'   => '0s',
            ]
        );

        self::$consul = new Consul($config);

        return $this;
    }
}
