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

use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;
use MilesAsylum\Slurp\Load\Exception\MissingValueException;
use PDO;
use PDOException;
use PDOStatement;

class BatchInsertManager implements BatchManagerInterface
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var PDOStatement[]
     */
    private $preparedBatchStmts = [];

    /**
     * @var string
     */
    private $database;

    public function __construct(
        PDO $pdo,
        string $table,
        array $columns,
        QueryFactory $queryFactory,
        string $database = null
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->columns = $columns;
        $this->queryFactory = $queryFactory;
        $this->database = $database;
    }

    /**
     * @throws LoadRuntimeException thrown if an error occurs writing rows to the database
     */
    public function write(array $rows): void
    {
        if (!empty($rows)) {
            $stmt = $this->getPreparedBatchStmt(count($rows));

            try {
                $stmt->execute($this->convertRowCollectionToParams($rows));
            } catch (PDOException $e) {
                throw new LoadRuntimeException('PDO exception thrown when inserting batch of records into staging table.', 0, $e);
            }
        }
    }

    protected function getPreparedBatchStmt($count): PDOStatement
    {
        if (!isset($this->preparedBatchStmts[$count])) {
            $this->preparedBatchStmts[$count] = $this->pdo->prepare(
                $this->queryFactory->createInsertQuery(
                    $this->table,
                    $this->columns,
                    $count,
                    $this->database
                )
            );
        }

        return $this->preparedBatchStmts[$count];
    }

    protected function ensureColumnMatch(int $rowId, array $rowValues): void
    {
        $missingFields = array_keys(
            array_diff_key(array_flip($this->columns), $rowValues)
        );

        if (count($missingFields)) {
            throw MissingValueException::createMissing($rowId, $missingFields);
        }
    }

    protected function convertRowCollectionToParams(array $rowCollection): array
    {
        $paramSets = [];

        foreach ($rowCollection as $rowId => $row) {
            $this->ensureColumnMatch($rowId, $row);
            $paramSets[] = $this->convertRowToParams($row);
        }

        return array_merge(...$paramSets);
    }

    protected function convertRowToParams(array $row): array
    {
        $params = [];

        foreach ($this->columns as $col) {
            $params[] = $row[$col];
        }

        return $params;
    }
}
