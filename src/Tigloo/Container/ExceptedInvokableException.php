<?php
declare(strict_types=1);


namespace Tigloo\Container;

use http\Exception\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Throwable;


class ExceptedInvokableException extends InvalidArgumentException implements ContainerExceptionInterface
{

}