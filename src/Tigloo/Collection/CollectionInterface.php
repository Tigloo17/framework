<?php

namespace Tigloo\Collection;

use ArrayAccess;
use Countable;
use Traversable;

interface CollectionInterface extends ArrayAccess, Countable
{
    public function set($key, $value): void;

    public function has($key): bool;

    public function all(): iterable;

    public function iterate(): Traversable;
}