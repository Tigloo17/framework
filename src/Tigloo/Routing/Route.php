<?php
declare(strict_types=1);


namespace Tigloo\Routing;


class Route
{
    private $method;
    private $name;
    private $pattern;
    private $action;
    private $default;
    private $params = [];
    private $middlewares = [];

    public function __construct(string $method, string $pattern, $action)
    {
        $this->default = function () use ($pattern) {
            throw new \Exception($pattern); // erreur 404 ??
        };

        $this->setMethod($method);
        $this->setPattern($pattern);
        $this->setAction($action);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName($name): Route
    {
        $this->name = $name;
        return $this;
    }

    public function withMiddlewares($middleware): Route
    {
        if (! is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middlewares = $middleware;
        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): Route
    {
        $this->pattern = '/'.ltrim(trim($pattern), '/');
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): Route
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action): Route
    {
        $this->action = empty($action) ? $this->default : $action;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): Route
    {
        $this->params = $params;
        return $this;
    }

    public function toArray(): array
    {
        return [
            $this->getMethod(),
            $this->getPattern(),
            clone $this,
            $this->getName()
        ];
    }
}