<?php

namespace YaangVu\Consul;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ConsulProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $uri   = config("consul.uri");
        $token = config("consul.token");
        $keys  = config("consul.keys");

        $client = new ConsulClient($uri, $token);

        foreach ($keys as $key) {
            $values = $client->get($key);
            Log::info($values);
        }
    }
}
