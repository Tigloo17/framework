<?php
declare(strict_types=1);

namespace Tigloo\Controller;


use Tigloo\Container\Container;


abstract class AbstractController
{
    private $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}