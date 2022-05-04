<?php
declare(strict_types=1);

namespace Tigloo\Base\Provider;


use Tigloo\Interfaces\ServiceProviderInterface;
use Tigloo\Container\Container;


class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app, array $parameters = [])
    {
        $app['path.twig'] = $app['path.resources'].DIRECTORY_SEPARATOR.'views';
        $app['twig'] = function ($app) {
            $twig = $app['twig.environment'];
            $twig->addGlobal('app', $app);
            $twig->addExtension(new \Twig\Extension\DebugExtension());
            $twig->addExtension(new \Tigloo\View\TwigExtension($app));
            return $twig;
        };

        $app['twig.loader_array'] = function () {
            return new \Twig\Loader\ArrayLoader([]);
        };

        $app['twig.loader_system'] = function ($app) {
            $loader = new \Twig\Loader\FilesystemLoader();
            $loader->addPath($app['path.twig']);
            return $loader;
        };
        
        $app['twig.loader'] = function ($app) {
            return new \Twig\Loader\ChainLoader([
                $app['twig.loader_array'],
                $app['twig.loader_system']
            ]);
        };

        $app['twig.environment'] = function ($app) {
            return new \Twig\Environment(
                $app['twig.loader'],
                [
                    'charset' => 'UTF-8',
                    'debug' => true,
                    'strict_variables' => false
                ]
            );
        };
    }
}