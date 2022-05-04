<?php
declare(strict_types=1);

namespace Tigloo\Base;

use Tigloo\Container\Container;
use Tigloo\Interfaces\BootableProviderInterface;
use Tigloo\Interfaces\ServiceProviderInterface;


class Application extends Container
{
    const VERSION = '1.0';

    private $booted = false;
    protected $providers = [];


    public static function version()
    {
        return static::VERSION;
    }
    
    public function __construct(string $pathname)
    {
        parent::__construct();

        $this->registerBaseContainer($pathname);
        $this->registerBaseProvider();
    }

    public function setPathEnvironment($pathenv): Application
    {
        if (! file_exists($pathenv)) {
            // erreur
        }

        $this['path.env'] = $pathenv;
        return $this;
    }

    public function register(ServiceProviderInterface $provider, array $parameters = [])
    {
        $this->providers[] = $provider;
        parent::register($provider, $parameters);
        
        return $this;
    }

    public function run(): void
    {
        if (file_exists($this['path.env'])) {
            $this['environments'] = (new FileLoader($this['path.env']))->load()->outputCollection();
        }
        $response = $this->handler();
        $response->send();
    }

    protected function handler()
    {
        if (! $this->booted) {
            $this->boot();
        }

        $this['routes']->flush();
        return $this['httpKernel']->handler($this['requests']);
    }

    protected function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;
        foreach ($this->providers as $provider) {
            if ($provider instanceof BootableProviderInterface) {
                $provider->boot($this);
            }
        }
    }

    private function registerBaseProvider(): void
    {
        $configuration = (new FileLoader($this['path.config']))->load()->outputCollection();
        if ($configuration->has('providers')) {
            $iterate = $configuration->providers->iterate();
            $iterate->rewind();
            while($iterate->valid()) {
                try {
                    $provider = $iterate->current();
                    $this->register(new $provider());
                } catch(\Exception $e) {
                    // log
                    throw new \RuntimeException($e->getMessage());
                }
                $iterate->next();
            }
        }
        
    }

    private function registerBaseContainer($pathbase): void
    {
        $this['path.base'] = rtrim($pathbase, '\/');
        $this['path.app'] = $this['path.base'].DIRECTORY_SEPARATOR.'app';
        $this['path.config'] = $this['path.base'].DIRECTORY_SEPARATOR.'config';
        $this['path.public'] = $this['path.base'].DIRECTORY_SEPARATOR.'public';
        $this['path.resources'] = $this['path.base'].DIRECTORY_SEPARATOR.'resources';
        $this['path.env'] = $this['path.base'].DIRECTORY_SEPARATOR.'.env';
        $this['path.logs'] = $this['path.base'].DIRECTORY_SEPARATOR.'logs';
    }
}