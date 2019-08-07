<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 7:11 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\PHPUnit;

use PDO;

class MySQLTestHelper
{
    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct()
    {
        $this->pdo = $this->connect($this->makeDsn());
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function getDatabaseName(): string
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE'];
    }

    public function getDatabaseUser(): ?string
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_USERNAME'] ?? null;
    }

    public function getDatabasePassword(): ?string
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PASSWORD'] ?? null;
    }

    public function getDatabaseHost(): ?string
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_HOST'] ?? null;
    }

    public function getDatabasePort(): int
    {
        return isset($_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PORT'])
            ? (int)$_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PORT']
            : 3306;
    }

    public function raiseTestSchema(): void
    {
        $database = $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE'];

        $this->pdo->exec(<<< SQL
DROP SCHEMA IF EXISTS `$database`
SQL
        );

        $this->pdo->exec(<<< SQL
CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci'
SQL
        );
    }

    protected function makeDsn(): string
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d',
            $this->getDatabaseHost(),
            $this->getDatabasePort()
        );

        return $dsn;
    }

    protected function connect($dsn): PDO
    {
        $pdo = new PDO($dsn, $this->getDatabaseUser(), $this->getDatabasePassword());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
