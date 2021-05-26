<?php

namespace YaangVu\Consul;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;

class ConsulProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $file = __DIR__ . '/../../../../config/consul.php';
        if (!file_exists($file))
            $file = __DIR__ . '/consul.php';

        $this->mergeConfigFrom($file, 'consul');
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws GuzzleException
     * @throws Exception
     */
    public function register()
    {
        if (file_exists(Constant::CONSUL_ENV_FILE))
            return;

//        $this->boot();

        $uri     = config("consul.uri");
        $token   = config("consul.token");
        $reqKeys = config("consul.keys");
        $scheme  = config("consul.scheme");
        $dc      = config("consul.dc");

        $client = new ConsulClient($uri, $token, $scheme, $dc);
        $consul = ConsulClient::$consul;

        $response = $consul->KV->Keys();
        if ($response->Err !== null)
            throw new Exception("Can not get consul keys");

        $resKeys   = $response->Value;
        $envString = '';

        foreach ($reqKeys as $reqKey) {
            foreach ($resKeys as $resKey) {
                if (Str::endsWith($resKey, '/') || !Str::startsWith($resKey, $reqKey))
                    continue;

                $envValue  = $client->get($resKey);
                $envKey    = Arr::last(explode('/', $resKey));
                $envString .= $envKey . "=" . $envValue . "\n";
            }
        }

        $this->putEnvToDotEnv(Constant::CONSUL_ENV_FILE, $envString);

        $this->reloadEnv();
    }

    public function putEnvToDotEnv(string $file, string $env, $mode = FILE_APPEND | LOCK_EX)
    {
        if (file_exists($file))
            unlink($file);
        file_put_contents($file, $env, $mode);
    }

    function reloadEnv()
    {
        (new LoadEnvironmentVariables(
            dirname(dirname(dirname(dirname(__DIR__)))),
            '.env.consul'
        ))->bootstrap();
    }
}
