<?php
declare(strict_types=1);

namespace Tigloo\Collection;

use ArrayIterator;
use ReturnTypeWillChange;
use Tigloo\Interfaces\CollectionInterface;
use Traversable;

class Collection implements CollectionInterface
{
    private $collection = [];

    public function __construct($collection = [])
    {
        $this->collection = $collection;
    }

    public function all(): array
    {
        return $this->collection;
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->collection[$key];
        }
        return $default;
    }

    public function set($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    public function has($key): bool
    {
        return $this->offsetExists($key);
    }

    public function iterate(): Traversable
    {
        return new ArrayIterator($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    //#ReturnTypeWillChange
    public function offsetExists($key): bool
    {
        return isset($this->collection[$key]);
    }

    //#ReturnTypeWillChange
    public function offsetGet($key)
    {
        return $this->collection[$key];
    }

    //#ReturnTypeWillChange
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$key] = $value;
        }
    }

    //#ReturnTypeWillChange
    public function offsetUnset($key): void
    {
        unset($this->collection[$key]);
    }
}