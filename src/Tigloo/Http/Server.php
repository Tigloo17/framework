<?php
declare(strict_types=1);

namespace Tigloo\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest;
use Tigloo\Interfaces\ControllerInterface;
use Tigloo\Interfaces\RouterInterface;
use Tigloo\Interfaces\ServerInterface;
use Tigloo\Routing\Route;

class Server extends Request implements ServerInterface
{
    private $serverParams = [];
    private $cookieParams = [];
    private $queryParam = [];
    private $parsedBody;
    private $attributes = [];
    private $uploadedFiles = [];
    private $statusCode = 200;
    private $session;
    private $route;

    public static function create(RouterInterface $routes): ServerInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = ServerRequest::getUriFromGlobals();
        $headers = getallheaders();
        $body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';
        $session = Session::create($uri);
        $route = $routes->match($method, $uri);

        $server = new Static($method, $uri, $headers, $body, $protocol, $_SERVER, $session, $route);

        return $server
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(ServerRequest::normalizeFiles($_FILES));
    }

    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        array $serverParams = [],
        Session $session = null,
        ?Route $route = null
    ){
        $this->serverParams = $serverParams;
        $this->session = $session ?? new Session();
        $this->route = $route;

        if ($this->route) {
            $this->attributes = $this->route->getParams();
        }

        parent::__construct($method, $uri, $headers, $body, $version);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies): ServerInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): ServerInterface
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles): ServerInterface
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data): ServerInterface
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function withAttribute($name, $value = null): ServerInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name): ServerInterface
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }
        
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function getRoute(): ?Route
    {
        return $this->route; 
    }
}