<?php

namespace YaangVu\Consul;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
        $file = $this->app->configPath('consul.php');
        if (!file_exists($file))
            $file = __DIR__ . '/consul.php';

        $this->mergeConfigFrom($file, 'consul');

        $this->publishConfig();
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
        if (file_exists(base_path(Constant::CONSUL_ENV_FILE))) {
            $this->reloadEnv();

            return;
        }

        $this->boot();
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
            $envString .= "# $reqKey \n";
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
        if (app() instanceof Illuminate\Foundation\Application) // If is Laravel instance
            (new \Illuminate\Foundation\BootstrapLoadEnvironmentVariables(
                $this->app->basePath(),
                '.env.consul'
            ))->bootstrap();
        else if ((app() instanceof \Laravel\Lumen\Application)) // If is Lumen Instance
            (new \Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
                $this->app->basePath(),
                '.env.consul'
            ))->bootstrap();
        else
            Log::error("Can not load consul environment");
    }

    private function publishConfig()
    {
        $path = $this->getConfigPath();
        if (app() instanceof Illuminate\Foundation\Application) // If is Laravel instance
            $this->publishes([$path => config_path('consul.php')], 'config');
    }

    private function getConfigPath(): string
    {
        return __DIR__ . '/consul.php';
    }
}
