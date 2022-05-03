<?php

namespace Tigloo\Interfaces;


use ArrayAccess;
use Countable;

interface CollectionInterface extends ArrayAccess, Countable
{
    public function all();

    public function get($key, $default = null);

    public function set($key, $value);

    public function iterate();
}
