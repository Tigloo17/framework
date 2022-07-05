<?php
declare(strict_types=1);

namespace Tigloo\Base\Provider;


use Tigloo\Interfaces\ServiceProviderInterface;
use Tigloo\Container\Container;
use Tigloo\Controller\Resolver;
use Tigloo\Http\HttpKernel;
use Tigloo\Http\Server;
use Tigloo\Interfaces\MiddlewareProviderInterface;

class HttpServiceProvider implements ServiceProviderInterface, MiddlewareProviderInterface
{
    public function register(Container $app, array $parameters = [])
    {
        $app['httpKernel'] = function ($app) {
            $route = $app['routes']->match(
                $app['request']->getMethod(),
                $app['request']->getUri()
            );

            return new HttpKernel($app['resolver'], $app, $route);
        };

        $app['requests'] = function ($app) {
            return Server::create();
        };

        $app['resolver'] = function ($app) {
            return new Resolver($app);
        };
    }

    public function subscribe(Container $app, HttpKernel $kernel)
    {
        // pipe middleware
    }
}