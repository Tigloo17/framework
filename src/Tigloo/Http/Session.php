<?php
declare(strict_types=1);

namespace Tigloo\Http;

use Psr\Http\Message\UriInterface;

class Session {

    private $_started;

    public static function create(UriInterface $uri, array $config = []): Session
    {
        if ($uri->getScheme() === 'https' && ini_get('session.cookie_secure') != 1) {
            $config['init']['session.cookie_secure'] = 1;
        }

        return new static($config ?? []);
    }

    public function __construct(array $config = [])
    {
        $config += [
            'timeout' => null,
            'init' => []
        ];

        if ($config['timeout']) {
            $config['ini']['session.gc_maxlifetime'] = 60 * $config['timeout'];
        }

        if (! isset($config['init']['session.cookie_path'])) {
            $cookiePath = empty($config['cookiePath']) ? '/' : $config['cookiePath'];
            $config['init']['session.cookie_path'] = $cookiePath;
        }

        $this->pushInit($config['init']);

        $this->_lifetime = (int) ini_get('session.gc_maxlifetime');

        session_register_shutdown();
    }

    public function start(): bool
    {
        if ($this->_started) {
            return true;
        }

        if (session_status() === \PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('La session est déjà active');
        }

        if (! session_start()) {
            throw new \RuntimeException('Impossible de démarrer la session');
        }

        return $this->_started = true;
    }

    public function restart(): void
    {
        if (! $this->hasSession()) {
            return;
        }

        $this->start();
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

        if (session_id() !== '') {
            session_regenerate_id(true);
        }
    }

    public function close(): bool
    {
        if (! $this->_started) {
            return true;
        }

        if (! session_write_close()) {
            throw new \RuntimeException('Impossible de fermer la session');
        }

        $this->_started = false;

        return true;
    }

    public function started(): bool
    {
        return $this->_started || session_status() === \PHP_SESSION_ACTIVE;
    }

    public function has($name): bool
    {
        if ($this->hasSession() && ! $this->started()) {
            $this->start();
        }

        return isset($_SESSION[$name]);
    }

    public function get(?string $name = null, $default = null)
    {   
        if ($this->hasSession() && ! $this->started()) {
            $this->start();
        }

        if (is_null($name)) {
            return $_SESSION ?: [];
        }

        if ($this->has($name)) {
            return $_SESSION[$name];
        }

        return $default;
    }

    public function set($name, $value = null): Session
    {
        if (! $this->_started) {
            $this->start();
        }

        $_SESSION[$name] = $value;
        return $this;
    }

    public function delete($name): void
    {
        if ($this->has($name)) {
            unset($_SESSION[$name]);
        }
    }

    public function id(?string $id = null)
    {
        return session_id();
    }

    public function destroy(): void
    {
        if ($this->hasSession() && ! $this->started()) {
            $this->start();
        }

        if (session_status() === \PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $_SESSION = [];
        $this->_started = false;
    }

    public function clear(bool $restart = false): void
    {
        $_SESSION = [];
        if ($restart) {
            $this->restart();
        }
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    protected function hasSession(): bool
    {
        return ! ini_get('session.use_cookies')
            || isset($_COOKIE[session_name()]);
    }

    private function pushInit(array $config): void
    {
        if (session_status() === \PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }

        foreach ($config as $setting => $value) {
            if (ini_set($setting, (string) $value) === false) {
                throw new \RuntimeException(
                    sprintf("Impossible de configurer la session avec le paramètre %s", $setting)
                );
            }
        }
    } 
}