<?php

namespace Tigloo\Interfaces;


use Psr\Http\Message\ServerRequestInterface;
use Tigloo\Interfaces\RouterInterface;
use Tigloo\Routing\Route;
use Tigloo\Http\Session;
use Tigloo\Http\Server;

interface ServerInterface extends ServerRequestInterface
{
    
    public static function create(RouterInterface $routes): ServerInterface;

    public function getSession(): ?Session;

    public function getRoute(): ?Route;
}