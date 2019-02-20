<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 3:29 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\DatabaseLoaderException;

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
     * @return string The name of the temporary table to insert staged data into.
     * @throws DatabaseLoaderException
     */
    public function begin(): string
    {
        if ($this->hasBegun) {
            throw new DatabaseLoaderException(
                'Staged-load has already been begun.'
            );
        }

        $this->hasBegun = true;
        $this->dropTemporaryTable($this->stageTable, $this->database);
        $this->createTemporaryTable($this->table, $this->stageTable, $this->database);

        return $this->stageTable;
    }

    public function discard(): void
    {
        if (!$this->hasBegun) {
            throw new DatabaseLoaderException(
                'Staged-load cannot be discarded without first being begun.'
            );
        }

        $this->dropTemporaryTable($this->stageTable, $this->database);
        $this->hasBegun = false;
    }

    public function commit(): void
    {
        if (!$this->hasBegun) {
            throw new DatabaseLoaderException(
                'Staged-load cannot be committed without first being begun.'
            );
        }

        $tickedTableRef = $this->createTickedTableRef($this->table, $this->database);
        $tickedStageTable = $this->createTickedTableRef($this->stageTable, $this->database);
        $cols = '`' . implode('`, `', $this->columns) . '`';

        $updateValues = [];

        foreach ($this->columns as $column) {
            $updateValues[] = "`{$column}` = VALUES(`{$column}`)";
        }

        $updateValuesStr = implode(', ', $updateValues);

        $this->pdo->exec(<<<SQL
INSERT INTO {$tickedTableRef} ({$cols})
  SELECT {$cols} FROM {$tickedStageTable}
  ON DUPLICATE KEY UPDATE {$updateValuesStr}
SQL
        );

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

        if (strlen($database)) {
            $tableRefTicked = "`{$database}`." . $tableRefTicked;
        }

        return $tableRefTicked;
    }
}
