<?php

namespace Tigloo\Interfaces;


use Tigloo\Container\Container;
use Tigloo\Http\HttpKernel;

interface MiddlewareProviderInterface
{
    public function subscribe(Container $app, HttpKernel $kernel);
}