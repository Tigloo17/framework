<?php
declare(strict_types=1);

/**
 * @source https://github.com/silexphp/Pimple/blob/main/src/Pimple/Container.php
 */
namespace Tigloo\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use SplObjectStorage;
use Countable;
use ArrayAccess;
use ReturnTypeWillChange;
use Tigloo\Interfaces\ServiceProviderInterface;


class Container implements ContainerInterface, Countable, ArrayAccess
{
    private $_factories;
    private $_instances = [];
    private $_aliases = [];

    public function __construct(array $data = [])
    {
        $this->_factories = new SplObjectStorage();

        foreach ($data as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    //#ReturnTypeWillChange
    public function offsetExists($id)
    {
        return isset($this->_aliases[$id]);
    }

    /**
     * @todo à modifier car bizarre comme fonction...???
     *
     * @param mixed $id
     * @return mixed
     */
    //#ReturnTypeWillChange
    public function offsetGet($id)
    {

        if (! $this->offsetExists($id)) {
            throw new UnknownIdentifierException($id);
        }

        if (
            ! is_object($this->_instances[$id])
            || ! method_exists($this->_instances[$id], '__invoke')
        ) {
            return $this->_instances[$id];
        }

        if (isset($this->_factories[$this->_instances[$id]])) {
            return $this->_instances[$id]($this);
        }

        $val = $this->_instances[$id] = $this->_instances[$id]($this);
        return $val;
    }

    //#ReturnTypeWillChange
    public function offsetSet($id, $value)
    {
        $this->_instances[$id] = $value;
        $this->_aliases[$id] = true;
    }

    //#ReturnTypeWillChange
    public function offsetUnset($id)
    {
        if ($this->offsetExists($id)) {
            unset($this->_aliases[$id], $this->_instances[$id]);
        }
    }

    //#ReturnTypeWillChange
    public function count(): int
    {
        return count(array_keys($this->_aliases));
    }

    public function factory($callable)
    {
        if (! is_object($callable) || ! method_exists($callable, '__invoke')) {
            throw new ExceptedInvokableException('Service definition is not a Closure or invokable object.');
        }

        $this->_factories->attach($callable);
        return $callable;
    }

    public function getAliases(): array
    {
        return array_keys($this->_aliases);
    }

    public function register(ServiceProviderInterface $provider, array $parameters = [])
    {
        $provider->register($this, $parameters);
    }
}