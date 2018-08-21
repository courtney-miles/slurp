<?php
/**
 * Author: Courtney Miles
 * Date: 17/08/18
 * Time: 5:58 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\LoaderInterface;
use PDO;

class DatabaseLoader implements LoaderInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var InsertUpdateSql
     */
    private $queryFactory;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var array[]
     */
    protected $mapping = [];

    /**
     * @var BatchWriter[]
     */
    protected $batchStatements = [];

    /**
     * @var array[]
     */
    protected $batchValues = [];
    /**
     * @var LoaderFactory
     */
    private $loaderFactory;

    public function __construct(
        PDO $pdo,
        LoaderFactory $loaderFactory,
        InsertUpdateSql $queryFactory,
        int $batchSize = 100
    ) {
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
        $this->batchSize = $batchSize;
        $this->loaderFactory = $loaderFactory;
    }

    public function addDestinationTable(string $table, array $columns) : void
    {
        $this->batchStatements[$table] = $this->loaderFactory->createBatchDatabaseInsert(
            $this->pdo,
            $this->queryFactory,
            $table,
            $columns
        );
        $this->mapping[$table] = $columns;
    }

    public function loadRow(array $row) : void
    {
        $this->ensureTransactionOpen();

        foreach ($this->batchStatements as $table => $batchStatement) {
            $batchStatement->addRowValues(array_intersect_key($row, array_flip($this->mapping[$table])));
        }
    }

    public function finalise() : void
    {
        $this->ensureTransactionOpen();

        foreach ($this->batchStatements as $table => $batchStatement) {
            $batchStatement->flush();
        }

        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    protected function ensureTransactionOpen() : void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }
}
