<?php
/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER
*/

namespace Magepattern\Component\Database;

use Magepattern\Component\Debug\Logger;

class Layer
{
    /**
     * @var Layer|null
     */
    protected static ?Layer $_instance = null;

    /**
     * @var \PDO|null Instance unique de la connexion PDO
     */
    protected ?\PDO $pdo = null;

    /**
     * @var mixed Driver instance (MySQL, SQLite, etc.)
     */
    public $adapter;

    /**
     * @var array Configuration active
     */
    public array $config = [];

    /**
     * @var array Options par défaut
     */
    protected array $setOption = [
        'mode'        => 'assoc',
        'closeCursor' => true,
        'debugParams' => false
    ];
    /**
     * @var bool
     */
    protected bool $inTransaction = false;
    /**
     * @var bool
     */
    protected bool $isPrepared = false;

    /**
     * Layer constructor.
     * @param array $config Configuration spécifique pour cette instance.
     */
    public function __construct(array $config = [])
    {
        $defaultDriver = defined('MP_DBDRIVER') ? MP_DBDRIVER : 'mysql';

        $defaults = [
            'driver'  => $defaultDriver,
            'charset' => 'utf8mb4',
            'port'    => 3306
        ];

        $this->config = array_merge($defaults, $config);

        try {
            if ($this->config['driver'] === 'sqlite' && !extension_loaded('pdo_sqlite')) {
                throw new \RuntimeException('Extension PDO SQLite not loaded');
            }
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
    }

    /**
     * Accès au Singleton.
     */
    public static function getInstance(): self
    {
        return self::$_instance ??= new self();
    }

    /**
     * Charge la connexion via l'adapter.
     * @return \PDO|null
     */
    /**
     * Charge la connexion via l'adapter en garantissant une instance unique (Singleton PDO).
     * @return \PDO|null
     */
    public function connection(): ?\PDO
    {
        try {
            // 1. Si la connexion PDO est déjà ouverte et mémorisée, on la retourne immédiatement !
            if ($this->pdo !== null) {
                return $this->pdo;
            }

            // 2. Sinon, on instancie l'adapter si ce n'est pas fait
            if (!$this->adapter) {
                $this->adapter = match ($this->config['driver']) {
                    'mysql', 'mariadb' => new MySQL(),
                    'pgsql'            => new PostgreSQL(),
                    'sqlite'           => new SQLite(),
                    default            => throw new \InvalidArgumentException("Driver '{$this->config['driver']}' not supported.")
                };
            }

            // 3. On établit la connexion ET on la sauvegarde dans notre nouvelle propriété $pdo
            $this->pdo = $this->adapter->connect($this->config);

            return $this->pdo;

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return null;
        }
    }

    /**
     * @param string $mode
     * @return int
     */
    private function setMode(string $mode): int
    {
        return match ($mode) {
            'class'  => \PDO::FETCH_CLASS,
            'column' => \PDO::FETCH_NUM,
            default  => \PDO::FETCH_ASSOC
        };
    }

    /**
     * @param array $option
     * @return array
     */
    private function setConfig(array $option = []): array
    {
        return array_merge($this->setOption, $option);
    }

    // -------------------------------------------------------------------------
    // MÉTHODES PUBLIQUES
    // -------------------------------------------------------------------------
    /**
     * @param string $query
     * @return \PDOStatement|false
     */
    public function query(string $query): \PDOStatement|false
    {
        try {
            return $this->connection()?->query($query) ?: false;
        } catch (\PDOException $e) {
            Logger::getInstance()->log($e, 'statement');
            return false;
        }
    }

    /**
     * @param string $sql
     * @return \PDOStatement|false
     */
    public function prepare(string $sql): \PDOStatement|false
    {
        try {
            return $this->connection()?->prepare($sql) ?: false;
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'statement');
            return false;
        }
    }

    /**
     * @return \PDO|false
     */
    public function beginTransaction(): \PDO|false
    {
        if ($this->inTransaction) {
            return false;
        }

        $connection = $this->connection();
        if ($connection && $connection->beginTransaction()) {
            $this->inTransaction = true;
            return $connection;
        }
        return false;
    }

    /**
     * @param string $sql
     * @return int|false
     */
    public function exec(string $sql): int|false
    {
        return $this->connection()?->exec($sql) ?: false;
    }

    /**
     * @return void
     */
    public function commit(): void
    {
        if ($this->inTransaction) {
            $this->connection()?->commit();
            $this->inTransaction = false;
        }
    }

    /**
     * @return void
     */
    public function rollBack(): void
    {
        $conn = $this->connection();
        if ($conn && $conn->inTransaction()) {
            $conn->rollBack();
            $this->inTransaction = false;
        } else {
            Logger::getInstance()->log('Must call beginTransaction() before you can rollback', 'statement');
        }
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return mixed
     */
    private function execute(string $type, string $sql, array $execute = [], array $setOption = []): mixed
    {
        if (trim($sql) === '') {
            Logger::getInstance()->log('Empty request received', 'statement');
            return false;
        }

        try {
            $sql = preg_replace('/\s+/S', " ", $sql);
            $prepare = $this->prepare($sql);

            if ($prepare instanceof \PDOStatement) {
                $setConfig = $this->setConfig($setOption);

                if (in_array($type, ['fetchAll', 'fetch', 'fetchObject', 'fetchColumn', 'columnCount', 'rowCount'])) {
                    $prepare->setFetchMode($this->setMode($setConfig['mode']));
                }

                if (!empty($execute)) {
                    foreach ($execute as $param => $value) {
                        $data_type = match(gettype($value)) {
                            'integer' => \PDO::PARAM_INT,
                            'boolean' => \PDO::PARAM_BOOL,
                            'NULL'    => \PDO::PARAM_NULL,
                            default   => \PDO::PARAM_STR
                        };
                        $prepare->bindValue($param, $value, $data_type);
                    }
                }

                $prepare->execute();

                if ($setConfig['debugParams']) {
                    $prepare->debugDumpParams();
                }

                $result = match ($type) {
                    'fetchAll'    => $prepare->fetchAll(),
                    'fetch'       => $prepare->fetch(),
                    'fetchObject' => $prepare->fetchObject(),
                    'fetchColumn' => $prepare->fetchColumn(),
                    'columnCount' => $prepare->columnCount(),
                    'rowCount'    => $prepare->rowCount(),
                    default       => true,
                };

                if ($setConfig['closeCursor']) {
                    $prepare->closeCursor();
                }

                return $result;
            }

            throw new \RuntimeException("$type Error with SQL prepare \n $sql");
        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'statement');
            return false;
        }
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return array|false
     */
    public function fetchAll(string $sql, array $execute = [], array $setOption = []): array|false
    {
        return $this->execute('fetchAll', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return array|false
     */
    public function fetch(string $sql, array $execute = [], array $setOption = []): array|false
    {
        return $this->execute('fetch', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return mixed
     */
    public function fetchColumn(string $sql, array $execute = [], array $setOption = []): mixed
    {
        return $this->execute('fetchColumn', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return object|false
     */
    public function fetchObject(string $sql, array $execute = [], array $setOption = []): object|false
    {
        return $this->execute('fetchObject', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return int|false
     */
    public function columnCount(string $sql, array $execute = [], array $setOption = []): int|false
    {
        return $this->execute('columnCount', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return int|false
     */
    public function rowCount(string $sql, array $execute = [], array $setOption = []): int|false
    {
        return $this->execute('rowCount', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $setOption
     * @return mixed
     */
    public function createTable(string $sql, array $setOption = []): mixed
    {
        return $this->execute('createTable', $sql, [], $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function insert(string $sql, array $execute = [], array $setOption = []): bool
    {
        return (bool)$this->execute('insert', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function update(string $sql, array $execute = [], array $setOption = []): bool
    {
        return (bool)$this->execute('update', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function delete(string $sql, array $execute = [], array $setOption = []): bool
    {
        return (bool)$this->execute('delete', $sql, $execute, $setOption);
    }

    /**
     * @param array $queries
     * @param array $config
     * @return array|false
     */
    public function transaction(array $queries, array $config = ['type' => 'prepare']): array|false
    {
        try {
            $transaction = $this->beginTransaction();
            if (!$transaction) {
                throw new \RuntimeException('Could not start transaction');
            }

            $rslt = [];
            foreach ($queries as $key => $value) {
                if ($config['type'] === 'prepare') {
                    if (!isset($value['request'])) {
                        throw new \InvalidArgumentException("Missing 'request' key at index $key");
                    }

                    $setConfig = $this->setConfig([]);
                    $prepare = $transaction->prepare($value['request']);

                    if ($prepare instanceof \PDOStatement) {
                        $prepare->setFetchMode($this->setMode($setConfig['mode']));
                        $prepare->execute($value['params'] ?? []);
                        $rslt[$key] = $prepare->fetchAll();

                        if ($setConfig['closeCursor']) {
                            $prepare->closeCursor();
                        }
                    } else {
                        throw new \RuntimeException("Prepare failed: " . $value['request']);
                    }
                } elseif ($config['type'] === 'exec') {
                    $rslt[$key] = $transaction->exec($value);
                }
            }

            $this->commit();
            return $rslt;

        } catch (\Throwable $e) {
            Logger::getInstance()->log($e, 'statement');
            $this->rollBack();
            return false;
        }
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $setOption
     * @return int|false
     */
    public function show(string $type, string $sql, array $setOption = []): int|false
    {
        $dbName = method_exists(self::class, 'getInfo') ? self::getInfo()->getDB() : ($this->config['dbname'] ?? '');

        $sql = match ($type) {
            'database' => 'SHOW DATABASES LIKE \'' . $sql . '\'',
            'table'    => 'SHOW TABLES FROM ' . $dbName . ' LIKE \'' . $sql . '\'',
            default    => ''
        };

        if (empty($sql)) {
            return false;
        }

        $setConfig = $this->setConfig($setOption);
        $prepare = $this->prepare($sql);

        if ($prepare instanceof \PDOStatement) {
            $prepare->execute();
            $result = $prepare->rowCount();
            if ($setConfig['closeCursor']) {
                $prepare->closeCursor();
            }
            return $result;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array $setOption
     * @return int|false
     */
    public function showTable(string $table, array $setOption = []): int|false
    {
        return $this->show('table', $table, $setOption);
    }

    /**
     * @param string $database
     * @param array $setOption
     * @return int|false
     */
    public function showDatabase(string $database, array $setOption = []): int|false
    {
        return $this->show('database', $database, $setOption);
    }

    /**
     * @param string $table
     * @param array $setOption
     * @return void
     */
    public function truncateTable(string $table, array $setOption = []): void
    {
        $sql = 'TRUNCATE TABLE ' . $table;
        $setConfig = $this->setConfig($setOption);
        $prepare = $this->prepare($sql);
        if ($prepare instanceof \PDOStatement) {
            $prepare->execute();
            if ($setConfig['closeCursor']) {
                $prepare->closeCursor();
            }
        }
    }

    /**
     * @return array
     */
    public function availableDrivers(): array
    {
        return $this->connection()?->getAvailableDrivers() ?: [];
    }

    /**
     * @return string|false
     */
    public function lastInsertId(): string|false
    {
        return $this->connection()?->lastInsertId() ?: false;
    }

    /**
     * @param string $string
     * @return string
     */
    public function quote(string $string): string
    {
        return $this->connection()?->quote($string) ?: "'$string'";
    }
}