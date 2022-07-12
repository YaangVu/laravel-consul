# Laravel Consul

`Laravel Consul` help you load config from `Consul` server

## Install

`composer require yaangvu/laravel-consul`

### Laravel

Register service in `providers` array in `config/app.php`

```
YaangVu\Consul\ConsulProvider::class
```

Publish consul configuration file

```
php artisan vendor:publish --provider="YaangVu\Consul\ConsulProvider"
```

### Lumen

Register service in `app/Providers/AppServiceProvider.php`

```php
 public function register()
    {
        $this->app->register(\YaangVu\Consul\ConsulProvider::class);
    }
```

Publish consul configuration file

``` shell
cp vendor/yaangvu/laravel-consul/src/consul.php config/consul.php
```

## Config

Append `.env` file with these configurations:

```dotenv
CONSUL_ENABLE=true
CONSUL_URI=${CONSUL_URI}
CONSUL_TOKEN=${CONSUL_TOKEN}
CONSUL_SCHEME=${CONSUL_SCHEME}
CONSUL_DC=${CONSUL_DC}
CONSUL_PATH=${CONSUL_PATH}
```
