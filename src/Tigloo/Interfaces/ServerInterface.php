<?php

namespace Tigloo\Interfaces;


use Psr\Http\Message\ServerRequestInterface;
use Tigloo\Interfaces\RouterInterface;
use Tigloo\Routing\Route;
use Tigloo\Http\Session;

interface ServerInterface extends ServerRequestInterface
{
    
    public static function create(): ServerInterface;

    public function getSession(): ?Session;
}