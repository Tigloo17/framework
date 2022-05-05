<?php
declare(strict_types=1);

namespace Tigloo\Databases;

use PDO;
use PDOException;

class Mysql
{
    protected $connection;
    protected $_baseConfig = [
        'persistent' => true,
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => '',
        'port' => '3306',
        'flags' => [],
        'encoding' => 'utf8mb4',
        'timezone' => 'UTC',
        'init' => [],
    ];

    public function __construct(array $config = [])
    {
        $this->configuration = $config += $this->_baseConfig;
    }

    public function connect()
    {
        if (! extension_loaded('pdo')) {
            throw new \RuntimeException('L\'extension PDO n\'est pas chargée');
        }

        if ($this->connection) {
            return true;
        }

        $configuration = $this->configuration;
        $configuration['flags'] += [
            PDO::ATTR_PERSISTENT => $configuration['persistent'],
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        
        if ($configuration['timezone'] === 'UTC') {
            $configuration['timezone'] = '+0:00';
        }

        if (isset($configuration['timezone'])) {
            $configuration['init'][] = sprintf("SET GLOBAL time_zone = '%s'", $configuration['timezone']);
        }

        $dns = "mysql:host={$configuration['host']};port={$configuration['port']};dbname={$configuration['database']}";
        if (! empty($configuration['encoding'])) {
            $dns.= ";charset={$configuration['encoding']}";
        }

        $this->_connect($dns, $configuration);

        if (! empty($configuration['init'])) {
            $connection = $this->getConnection();
            foreach ((array) $configuration['init'] as $command) {
                $connection->exec($command);
            }
        }

        return true;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function close(): void
    {
        $this->connection = null;
    }

    public function isConnected(): bool
    {
        if ($this->connection === null) {
            $connected = false;
        } else {
            try {
                $connected = (bool) $this->connection->query('SELECT 1');
            } catch (PDOException $e) {
                $connected = false;
            }
        }
        return $connected;
    }

    public function from(string $table)
    {
        $query = new Select($this, $table);
    }

    public function insert(string $table)
    {
        $query = new Insert($this, $table);
    }

    public function update(string $table)
    {
        $query = new Update($this, $table);
    }

    public function delete(string $table)
    {
        $query = new Delete($this, $table);
    }

    private function _connect(string $dns, array $config): void
    {
        try {
            $this->connection = new PDO(
                $dns,
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config
            );
        } catch(\PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}