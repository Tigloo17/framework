<?php
declare(strict_types=1);

namespace Tigloo\Base\Provider;


use Tigloo\Container\Container;
use Tigloo\Databases\Mysql;
use Tigloo\Interfaces\ServiceProviderInterface;


class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app, array $parameters)
    {
        $app['db.config'] = function ($app) {
            $env = $app['environments'];
            return [
                'host' => $env->DB_HOST ?? 'localhost',
                'username' => $env->DB_USERNAME ?? 'root',
                'password' => $env->DB_PASSWORD ?? '',
                'database' => $env->DB_DATABASE ?? '',
                'port' => $env->DB_PORT ?? '3306',
            ];
        };

        $app['db'] = function ($app) {
            return new Mysql($app['db.config']);
        };
    }
}