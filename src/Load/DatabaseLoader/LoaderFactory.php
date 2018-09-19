<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 3:34 PM
 */

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

    public function createBatchInsertManager(string $table, array $columns): BatchInsertManager
    {
        return new BatchInsertManager($this->pdo, $table, $columns, $this->createQueryFactory());
    }

    public function createStagedLoad(string $table, array $columns): StagedLoad
    {
        return new StagedLoad($this->pdo, $table, $columns);
    }

    protected function createQueryFactory(): QueryFactory
    {
        return new QueryFactory();
    }
}
