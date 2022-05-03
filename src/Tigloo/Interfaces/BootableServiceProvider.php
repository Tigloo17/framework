<?php

namespace Tigloo\Interfaces;


use Tigloo\Container\Container;


interface BootableProviderInterface
{
    public function boot(Container $app);
}