<?php
declare(strict_types=1);

namespace Tigloo\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tigloo\Controller\Resolver;
use Tigloo\Container\Container;
use Tigloo\Routing\Route;
use RuntimeException;
use SplQueue;


class HttpKernel implements RequestHandlerInterface, MiddlewareInterface
{
    protected $resolver;
    protected $app;
    private $route;
    private $queue;

    public function __construct(Resolver $resolver, Container $app, ?Route $route)
    {
        $this->resolver = $resolver;
        $this->app = $app;
        $this->route = $route;
        $this->queue = new SplQueue();

        if (! is_null($this->route) && $this->route->hasMiddlewares()) {
            foreach ($this->route->getMiddlewares() as $middleware) {
                $this->pipe($middleware);
            }
        }
    }

    public function pipe(MiddlewareInterface $middleware): HttpKernel
    {
        $this->queue->enqueue($middleware);
        return $this;
    }

    public function app(): Container
    {
        return $this->app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->queue->rewind();
        if (! $this->queue->isEmpty()) {
            $middleware = $this->queue->current();
            $this->queue->dequeue();

            if ($middleware instanceof MiddlewareInterface) {
                return $middleware->process($request, clone $this);
            }
        }

        return $this->process($request, clone $this);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withHeader('X-Php-Ob-Level', (string) ob_get_level());
        try {
            
            if (null === $this->route) {
                throw new RuntimeException('NotFound', 404);
            }

            $controller = $this->resolver->getController($this->route);
            $arguments = $this->resolver->getArguments($request, $controller);

            $response = $controller($arguments);

            if (! $response instanceof ResponseInterface) {
                $response = new Response($response, $request->getHeaders());
                // @todo Retranscription dans un middleware
                // @todo la fonction est un void et ne fait que préparer des headers de base...
                $response = $response->prepare($request);
            }

            return $response;

        } catch(\Exception $e) {
            return $this->handleThrowable($e, $request);
        }
    }

    public function handleThrowable(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        // @todo voir pour faire un system d'erreur...
        var_dump($e->getCode(), $e->getMessage());
        return new Response();
    }
}