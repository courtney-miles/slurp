<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

class LoaderFactory
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createBatchInsertManager(string $table, array $columns, ?string $database = null): BatchInsertManager
    {
        return new BatchInsertManager($this->pdo, $table, $columns, $this->createQueryFactory(), $database);
    }

    public function createStagedLoad(string $table, array $columns, ?string $database = null): StagedLoad
    {
        return new StagedLoad($this->pdo, $table, $columns, $database);
    }

    protected function createQueryFactory(): QueryFactory
    {
        return new QueryFactory();
    }
}
