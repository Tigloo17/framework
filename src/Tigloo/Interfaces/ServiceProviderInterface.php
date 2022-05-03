<?php

namespace Tigloo\Interfaces;


use Tigloo\Container\Container;


interface ServiceProviderInterface
{
    public function register(Container $app, array $parameters);
}