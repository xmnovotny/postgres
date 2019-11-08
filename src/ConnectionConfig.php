<?php

namespace Amp\Postgres;

use Amp\Sql\ConnectionConfig as SqlConnectionConfig;

final class ConnectionConfig extends SqlConnectionConfig
{
    const KEY_MAP = [
        'username' => 'user',
        'pass' => 'password',
        'database' => 'db',
        'dbname' => 'db',
        'options' => 'options'
    ];

    const DEFAULT_PORT = 5432;

    /** @var string|null */
    private $string;
    /** @var string|null */
    private $options;

    public static function fromString(string $connectionString): self
    {
        $parts = self::parseConnectionString($connectionString, self::KEY_MAP);

        if (!isset($parts["host"])) {
            throw new \Error("Host must be provided in connection string");
        }

        return new self(
            $parts["host"],
            (int) ($parts["port"] ?? self::DEFAULT_PORT),
            $parts["user"] ?? null,
            $parts["password"] ?? null,
            $parts["db"] ?? null,
            $parts["options"] ?? null
        );
    }

    public function __construct(
        string $host,
        int $port = self::DEFAULT_PORT,
        string $user = null,
        string $password = null,
        string $database = null,
        string $appName = null
    ) {
        $this->options = $appName;
        parent::__construct($host, $port, $user, $password, $database);
    }

    public function __clone()
    {
        $this->string = null;
    }

    /**
     * @return string Connection string used with ext-pgsql and pecl-pq.
     */
    public function getConnectionString(): string
    {
        if ($this->string !== null) {
            return $this->string;
        }

        $chunks = [
            "host=" . $this->getHost(),
            "port=" . $this->getPort(),
        ];

        $user = $this->getUser();
        if ($user !== null) {
            $chunks[] = "user=" . $user;
        }

        $password = $this->getPassword();
        if ($password !== null) {
            $chunks[] = "password=" . $password;
        }

        $database = $this->getDatabase();
        if ($database !== null) {
            $chunks[] = "dbname=" . $database;
        }

        $options = $this->getOptions();
        if ($options !== null) {
            $chunks[] = 'options='.$options;
        }

        return $this->string = \implode(" ", $chunks);
    }

    final public function getOptions():?string {
        return $this->options;
    }

    final public function withOptions(string $options=null): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

}
