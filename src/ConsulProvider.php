<?php

namespace YaangVu\Consul;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;

class ConsulProvider extends ServiceProvider
{
    public string       $configPath;
    public string       $envPath;
    public ConsulClient $client;
    public array        $directories = [];
    public string       $uri;
    public string       $token;
    public string       $scheme;
    public string       $dc;
    public array        $needKeys;
    public bool         $recursive;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->configPath = $this->app->configPath(Constant::CONSUL_CONFIG_FILE);
        $this->envPath    = $this->app->basePath(Constant::CONSUL_ENV_FILE);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
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
        if (file_exists($this->envPath)) {
            $this->reloadEnv();

            return;
        }

        $this->_config();

        $this->client = new ConsulClient($this->uri, $this->token, $this->scheme, $this->dc);
        $response     = ConsulClient::$consul->KV->Keys();
        if ($response->Err !== null)
            throw new Exception("Can not get consul keys");

        $consulKeys = $response->Value; // List of Consul Keys available
        $envString  = '';
        foreach ($this->needKeys as $needKey) {
            $needKey             = trim($needKey, '/');
            $this->directories[] = "$needKey";
            $envString           .= "# /$needKey/ \n";
            foreach ($consulKeys as $consulKey) {
                $envString = $this->_genEnvString($needKey, $consulKey, $envString, $this->recursive);
            }
        }

        $this->putEnvToDotEnv($this->envPath, $envString);

        $this->reloadEnv();
    }

    private function _genEnvString(string $needKey, string $consulKey, string &$envString = '',
                                   bool   $recursive = true): string
    {
        // If not match to $needKey
        if (!Str::startsWith($consulKey, $needKey))
            return $envString;

        // If it can be not recursive, reject if level > 1
        $replacedKey = Str::replaceFirst("$needKey/", '', $consulKey);
        if (!$recursive && Str::contains($replacedKey, '/'))
            return $envString;

        $dir = Str::of($consulKey)->dirname();
        // Add directory to comment if not exist
        if (!in_array($dir, $this->directories)) {
            $envString           .= "# /$dir/ \n";
            $this->directories[] = $dir;
        }

        // Get value of Consul Key via API
        $envKey    = Str::of($consulKey)->basename();
        $envValue  = $this->client->get($consulKey);
        $envString .= $envKey . "=" . $envValue . "\n";

        return $envString;
    }

    public function putEnvToDotEnv(string $file, string $env, $mode = FILE_APPEND | LOCK_EX)
    {
        file_put_contents($file, $env, $mode);
    }

    function reloadEnv()
    {
        if (app() instanceof \Illuminate\Foundation\Application) // If is Laravel instance
            app()->loadEnvironmentFrom($this->envPath);
        else if ((app() instanceof \Laravel\Lumen\Application)) // If is Lumen Instance
            (new LoadEnvironmentVariables($this->app->basePath(), Constant::CONSUL_ENV_FILE))->bootstrap();
        else
            Log::error("Can not load consul environment");
    }

    private function publishConfig()
    {
        $path = $this->_getConfigPath();
        if (app() instanceof \Illuminate\Foundation\Application) // If is Laravel instance
            $this->publishes([$path => config_path('consul.php')], 'config');
    }

    private function _getConfigPath(): string
    {
        return __DIR__ . '/' . Constant::CONSUL_CONFIG_FILE;
    }

    private function _config()
    {
        $path = file_exists($this->configPath) ? $this->configPath : $this->_getConfigPath();

        $this->mergeConfigFrom($path, 'consul');

        $this->uri       = config("consul.uri");
        $this->token     = config("consul.token");
        $this->needKeys  = config("consul.keys") ?? [];
        $this->scheme    = config("consul.scheme");
        $this->dc        = config("consul.dc");
        $this->recursive = config("consul.recursive") ?? false;
    }

    private function _getFolder(string $consulKey): string
    {
        $key = last(explode('/', trim($consulKey, '/')));

        return Str::before($consulKey, $key);
    }
}
