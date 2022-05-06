<?php
declare(strict_types=1);

namespace Tigloo\Controller;


use GuzzleHttp\Psr7\Utils;
use Tigloo\Container\Container;
use Tigloo\Databases\Mysql;
use Tigloo\Http\Response;


abstract class AbstractController
{
    private $app;

    public function setContainer(Container $app): AbstractController
    {
        $this->$app = $app;
        return $this;
    }

    /**
     * Accès à la connection de la base de donnée via le controller
     *
     * @return Mysql|null
     */
    public function db(): ?Mysql
    {
        if ($this->app->has('db')) {
            $database = $this->app['db'];
            $database->connect();
            return $database;
        }
        return null;
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