<?php

namespace YaangVu\Consul;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ConsulProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws GuzzleException
     */
    public function register()
    {
        $uri    = config("consul.uri");
        $token  = config("consul.token");
        $keys   = config("consul.keys");
        $scheme = config("consul.scheme");
        $dc     = config("consul.dc");

        $client = new ConsulClient($uri, $token, $scheme, $dc);

        $envString = '';
        foreach ($keys as $key)
            if (Str::endsWith($key, '/'))
                continue;
            else {
                $envKey    = Arr::last(explode('/', $key));
                $envValue  = $client->get($key);
                $envString .= $envKey . "=" . $envValue . "\n";
            }

        $this->putEnvToDotEnv(Constant::CONSUL_ENV_FILE, $envString);
    }

    public function putEnvToDotEnv(string $file, string $env, $mode = FILE_APPEND | LOCK_EX)
    {
        file_put_contents($file, $env, $mode);
    }
}
