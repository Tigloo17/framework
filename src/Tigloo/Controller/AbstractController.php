<?php
declare(strict_types=1);

namespace Tigloo\Controller;


use GuzzleHttp\Psr7\Utils;
use Tigloo\Container\Container;
use Tigloo\Http\Response;


abstract class AbstractController
{
    private $container;

    public function setContainer(Container $container): AbstractController
    {
        $this->container = $container;
        return $this;
    }

    public function render(string $render, array $parameters = [], ?Response $response = null): Response
    {
        $twig = $this->container->get('twig')->render($render, $parameters);
        if (is_null($response)) {
            $response = new Response();
        }
        
        $response = $response->withBody(Utils::streamFor($twig));
        return $response;
    }

    public function json($data = null, int $statusCode = 200, ?Response $response = null): Response
    {
        return new Response();
    }
}