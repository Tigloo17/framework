<?php
declare(strict_types=1);

namespace Tigloo\Controller;


use Reflector;
use Tigloo\Container\Container;

class Controller
{
    private $reflector;
    private $container;

    public function __construct(Reflector $reflector, Container $container = null)
    {
        $this->reflector = $reflector;
        $this->container = $container;
    }

    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    public function __invoke(array $arguments = [])
    {
        if ($this->reflector->isClosure()) {
            $controller = $this->reflector->invokeArgs($arguments);
        } else {
            $class = $this->reflector->getDeclaringClass()->getName();
            $instanciate = new $class();

            if ($instanciate instanceof AbstractController) {
                $instanciate = $instanciate->setContainer($this->container);
            }

            $controller = $this->reflector->invokeArgs($instanciate, $arguments);
        }

        return $controller;
    }
}