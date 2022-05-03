<?php
declare(strict_types=1);

namespace Tigloo\Base\Provider;


use Tigloo\Interfaces\ServiceProviderInterface;
use Tigloo\Container\Container;


class RouteServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app, array $parameters = [])
    {
        $app['path.routes'] = $app['path.app'].DIRECTORY_SEPARATOR.'routes.php';
        
        $app['routes.factory'] = $app->factory(function ($app) {
            return new \Tigloo\Routing\Router($app['path.routes']);
        });

        $app['routes'] = function ($app) {
            return $app['routes.factory'];
        };
    }
}