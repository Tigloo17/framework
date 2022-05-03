<?php
declare(strict_types=1);


namespace Tigloo\Routing;


final class RouteCollection
{
    protected $routes = [];

    public function get($pattern, $action): Route
    {
        return $this->addRoute('GET', $pattern, $action);
    }

    public function post($pattern, $action): Route
    {
        return $this->addRoute('POST', $pattern, $action);
    }

    public function put($pattern, $action): Route
    {
        return $this->addRoute('PUT', $pattern, $action);
    }

    public function delete($pattern, $action): Route
    {
        return $this->addRoute('DELETE', $pattern, $action);
    }

    public function options($pattern, $action): Route
    {
        return $this->addRoute('OPTIONS', $pattern, $action);
    }

    public function patch($pattern, $action): Route
    {
        return $this->addRoute('PATCH', $pattern, $action);
    }

    public function all()
    {
        return $this->routes;
    }

    protected function addRoute($method, $pattern, $action): Route
    {
        $this->routes[] = $route = new Route($method, $pattern, $action);
        return $route;
    }
}