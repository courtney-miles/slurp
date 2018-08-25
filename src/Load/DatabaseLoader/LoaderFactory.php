<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 11:37 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

class LoaderFactory
{
    public function createBatchDatabaseInsert(
        \PDO $pdo,
        InsertUpdateSql $queryFactory,
        string $table,
        array $columns,
        int $batchSize = 100
    ) : DatabaseLoader {
        return new DatabaseLoader($pdo, $queryFactory, $table, $columns, $batchSize);
    }
}
