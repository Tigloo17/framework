<?php
declare(strict_types=1);

namespace Tigloo\Base\Provider;


use Tigloo\Interfaces\ServiceProviderInterface;
use Tigloo\Container\Container;
use Tigloo\Controller\Resolver;
use Tigloo\Http\HttpKernel;
use Tigloo\Http\Server;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app, array $parameters = [])
    {
        $app['httpKernel'] = function ($app) {
            return new HttpKernel($app['resolver']);
        };

        $app['requests'] = function ($app) {
            return Server::create($app['routes']);
        };

        $app['resolver'] = function ($app) {
            return new Resolver($app);
        };
    }
}