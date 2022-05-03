<?php
declare(strict_types=1);

namespace Tigloo\Controller;

use RuntimeException;
use Tigloo\Container\Container;
use Tigloo\Interfaces\ServerInterface;

class Resolver
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getController(ServerInterface $request)
    {
        $action = $request->getRoute()->getAction();
        
        if (is_string($action)) {
            if (! strpos($action, '#')) {
                throw new RuntimeException('NotFoundMethod', 500);
            }
            [$class, $method] = explode('#', $action);
            $controller = new Controller(new \ReflectionMethod(new $class(), $method), $this->container);
        } elseif (is_object($action) && ! $action instanceof \Closure) {
            $controller = new Controller((new \ReflectionObject($action))->getMethod('__invoke'), $this->container);
        } else {
            $controller = new Controller(new \ReflectionFunction($action), $this->container);
        }
        
        return $controller ?? null;
    }

    public function getArguments(ServerInterface $request, Controller $controller)
    {
        $arguments = [];
        $attributes = ('GET' !== $request->getMethod()) ? $request->getParsedBody() : $request->getAttributes();
        
        foreach ($controller->getReflector()->getParameters() as $args) {
            if ($args->isVariadic()) {
                $arguments[] = $attributes;
                continue;
            } else {
                $arg = null;
                foreach ($attributes as $k => $v) {
                    if ($args->getName() === $k) {
                        $arg = $v;
                        break;
                    }
                }

                if (! empty($arg)) {
                    $arguments[] = $arg;
                } else {
                    var_dump($args->allowsNull());

                    if ($args->isDefaultValueAvailable()) {
                        $arguments[] = $args->getDefaultValue();
                    } elseif ($args->allowsNull()) {
                        $arguments[] = null;
                    } else {
                        throw new \LogicException(sprintf("Aucune résolution de l'attribut (%s)", $args->getName()));
                    }
                }
            }
        }
        return $arguments;
    }
}