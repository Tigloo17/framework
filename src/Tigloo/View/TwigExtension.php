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
            new TwigFunction('Assets', [$this, 'assets'])
        ];
    }

    public function routeGenerate(string $name, array $attributes = [])
    {
        return 'route';
    }

    public function assets(string $file)
    {
        return 'asset';
    }
}