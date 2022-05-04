<?php
declare(strict_types=1);

namespace Tigloo\View;


use Tigloo\Container\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


final class TwigExtension extends AbstractExtension
{
    private $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('Route', [$this, 'routeGenerate']),
        ];
    }

    public function routeGenerate(string $name, array $attributes = [])
    {
        return $this->app['routes']->generate($name, $attributes);
    }
}