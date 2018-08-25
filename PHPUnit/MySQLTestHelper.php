<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 7:11 PM
 */

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
    public function getConnection()
    {
        return $this->pdo;
    }

    public function getDatabaseName()
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE'];
    }

    public function getDatabaseUser()
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_USERNAME'];
    }

    public function getDatabasePassword()
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PASSWORD'];
    }

    public function getDatabaseHost()
    {
        return $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_HOST'];
    }

    public function raiseTestSchema()
    {
        $database = $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_DATABASE'];

        $this->pdo->query(<<< SQL
DROP SCHEMA IF EXISTS `$database`
SQL
        );

        $this->pdo->query(<<< SQL
CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci'
SQL
        );
    }

    protected function makeDsn()
    {
        $dsn = sprintf(
            "mysql:host=%s;port=%d",
            $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_HOST'],
            !empty($_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PORT']) ? $_ENV['TESTS_SLURP_DBADAPTER_MYSQL_PORT'] : 3306
        );

        return $dsn;
    }

    protected function connect($dsn)
    {
        $pdo = new \PDO($dsn, $this->getDatabaseUser(), $this->getDatabasePassword());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
