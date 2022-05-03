<?php

namespace Tigloo\Interfaces;


use Psr\Http\Message\UriInterface;


interface RouterInterface
{
    public function flush(): void;

    public function match(string $method, UriInterface $uri);
}