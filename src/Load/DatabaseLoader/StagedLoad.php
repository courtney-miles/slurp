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

use MilesAsylum\Slurp\Exception\LogicException;
use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;

class StagedLoad
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $stageTable;

    /**
     * @var array
     */
    private $columns;

    protected $hasBegun = false;

    /**
     * @var string
     */
    private $database;

    public function __construct(\PDO $pdo, string $table, array $columns, string $database = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->stageTable = "_{$table}_stage";
        $this->columns = $columns;
        $this->database = $database;
    }

    /**
     * @return string the name of the temporary table to insert staged data into
     *
     * @throws LogicException
     */
    public function begin(): string
    {
        if ($this->hasBegun) {
            throw new LogicException('Staged-load has already been begun.');
        }

        $this->hasBegun = true;
        $this->dropTemporaryTable($this->stageTable, $this->database);
        $this->createTemporaryTable($this->table, $this->stageTable, $this->database);

        return $this->stageTable;
    }

    /**
     * @throws LogicException
     */
    public function discard(): void
    {
        if (!$this->hasBegun) {
            throw new LogicException('Staged-load cannot be discarded without first being begun.');
        }

        $this->dropTemporaryTable($this->stageTable, $this->database);
        $this->hasBegun = false;
    }

    /**
     * @throws LogicException
     */
    public function commit(): void
    {
        if (!$this->hasBegun) {
            throw new LogicException('Staged-load cannot be committed without first being begun.');
        }

        $tickedTableRef = $this->createTickedTableRef($this->table, $this->database);
        $tickedStageTable = $this->createTickedTableRef($this->stageTable, $this->database);
        $cols = '`' . implode('`, `', $this->columns) . '`';

        $updateValues = [];

        foreach ($this->columns as $column) {
            $updateValues[] = "`{$column}` = VALUES(`{$column}`)";
        }

        $updateValuesStr = implode(', ', $updateValues);

        try {
            $this->pdo->exec(<<<SQL
INSERT INTO {$tickedTableRef} ({$cols})
  SELECT {$cols} FROM {$tickedStageTable}
  ON DUPLICATE KEY UPDATE {$updateValuesStr}
SQL
            );
        } catch (\PDOException $e) {
            throw new LoadRuntimeException('PDO exception thrown when copying rows from the staging table to the destination table.', 0, $e);
        }

        $this->dropTemporaryTable($this->stageTable, $this->database);

        $this->hasBegun = false;
    }

    protected function createTemporaryTable($likeTable, $asTable, string $database = null): void
    {
        $tickedAsTable = $this->createTickedTableRef($asTable, $database);
        $tickedLikeTable = $this->createTickedTableRef($likeTable, $database);

        $this->pdo->exec(<<<SQL
CREATE TEMPORARY TABLE {$tickedAsTable} LIKE {$tickedLikeTable} 
SQL
        );
    }

    protected function dropTemporaryTable(string $table, string $database = null): void
    {
        $tickedTableRef = $this->createTickedTableRef($table, $database);
        $this->pdo->exec(<<<SQL
DROP TEMPORARY TABLE IF EXISTS {$tickedTableRef}
SQL
        );
    }

    protected function createTickedTableRef(string $table, string $database = null): string
    {
        $tableRefTicked = "`{$table}`";

        if (null !== $database && '' !== $database) {
            $tableRefTicked = "`{$database}`." . $tableRefTicked;
        }

        return $tableRefTicked;
    }
}
