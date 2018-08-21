<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 10:25 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\ColumnMismatchException;

class BatchWriter
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var InsertUpdateSql
     */
    private $queryFactory;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var \PDOStatement
     */
    protected $batchStmt;

    /**
     * @var \PDOStatement
     */
    protected $singleStmt;

    protected $rowCollection = [];
    /**
     * @var string
     */
    private $table;
    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        \PDO $pdo,
        InsertUpdateSql $queryFactory,
        string $table,
        array $columns,
        int $batchSize = 100
    ) {
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
        $this->table = $table;
        $this->columns = $columns;
        $this->batchSize = $batchSize;
        $this->batchStmt = $this->pdo->prepare(
            $this->queryFactory->createSql($this->table, $this->columns, $this->batchSize)
        );
        $this->singleStmt = $this->pdo->prepare(
            $this->queryFactory->createSql($this->table, $this->columns)
        );
    }

    public function addRowValues(array $rowValues): void
    {
        $this->ensureColumnMatch($rowValues);
        $this->rowCollection[] = $rowValues;

        if (count($this->rowCollection) == $this->batchSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (count($this->rowCollection) == $this->batchSize) {
            $params = $this->convertRowCollectionToParams($this->rowCollection);

            $this->batchStmt->execute($params);
            $this->rowCollection = [];
        } else {
            foreach ($this->rowCollection as $row) {
                $this->singleStmt->execute($this->convertRowToParams($row));
            }
        }
    }

    protected function ensureColumnMatch($rowValues): void
    {
        $expectedCount = count($this->columns);

        if (count($rowValues) != $expectedCount
            || count(array_intersect_key(array_flip($this->columns), $rowValues)) != $expectedCount
        ) {
            throw new ColumnMismatchException(
                sprintf(
                    'The supplied row has values for %s where it is expected to have values for %s.',
                    array_keys($rowValues),
                    $this->columns
                )
            );
        }
    }

    protected function convertRowCollectionToParams(array $rowCollection):array
    {
        $params = [];

        foreach ($rowCollection as $row) {
            $params = array_merge($params, $this->convertRowToParams($row));
        }

        return $params;
    }

    protected function convertRowToParams($row):array
    {
        $params = [];

        foreach ($this->columns as $col) {
            $params[] = $row[$col];
        }

        return $params;
    }
}
