<?php
declare(strict_types=1);


namespace Tigloo\Http;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Tigloo\Controller\Resolver;
use Tigloo\Interfaces\ServerInterface;


class HttpKernel
{
    protected $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handler(ServerInterface $request)
    {
        $request = $request->withHeader('X-Php-Ob-Level', (string) ob_get_level());

        try {
            return $this->handleRaw($request);
        } catch(\Exception $e) {
            return $this->handleThrowable($e, $request);
        }
    }

    public function handleRaw(ServerInterface $request): ResponseInterface
    {
        if ($request->getRoute() === null) {
            throw new RuntimeException('NotFound', 404);
        }

        $controller = $this->resolver->getController($request);
        $arguments = $this->resolver->getArguments($request, $controller);

        $response = $controller($arguments);
        
        if (! $response instanceof ResponseInterface) {
            $response = new Response($response, $request->getHeaders());
            $response = $response->prepare($request);
        }

        return $response;
    }

    public function handleThrowable(\Throwable $e, ServerInterface $request): ResponseInterface
    {
        // @todo voir pour faire un system d'erreur...
        var_dump($e->getCode(), $e->getMessage());
        return new Response();
    }
}