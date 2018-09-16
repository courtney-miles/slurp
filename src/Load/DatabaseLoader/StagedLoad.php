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

    public function __construct(\PDO $pdo, string $table, array $columns)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->stageTable = "_{$table}_stage";
        $this->columns = $columns;
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
        $this->dropTemporaryTable($this->stageTable);
        $this->createTemporaryTable($this->table, $this->stageTable);

        return $this->stageTable;
    }

    public function discard()
    {
        if (!$this->hasBegun) {
            throw new DatabaseLoaderException(
                'Staged-load cannot be discarded without first being begun.'
            );
        }

        $this->dropTemporaryTable($this->stageTable);
        $this->hasBegun = false;
    }

    public function commit()
    {
        if (!$this->hasBegun) {
            throw new DatabaseLoaderException(
                'Staged-load cannot be committed without first being begun.'
            );
        }

        $cols = '`' . implode('`, `', $this->columns) . '`';

        $updateValues = [];

        foreach ($this->columns as $column) {
            $updateValues[] = "`{$column}` = VALUES(`{$column}`)";
        }

        $updateValuesStr = implode(', ', $updateValues);

        $this->pdo->exec(<<<SQL
INSERT INTO `{$this->table}` ({$cols})
  SELECT {$cols} FROM `{$this->stageTable}`
  ON DUPLICATE KEY UPDATE {$updateValuesStr}
SQL
        );

        $this->dropTemporaryTable($this->stageTable);

        $this->hasBegun = false;
    }

    protected function createTemporaryTable($likeName, $asName)
    {
        $this->pdo->exec(<<<SQL
CREATE TEMPORARY TABLE `{$asName}` LIKE `{$likeName}`
SQL
        );
    }

    protected function dropTemporaryTable($name)
    {
        $this->pdo->exec(<<<SQL
DROP TEMPORARY TABLE IF EXISTS `{$name}`
SQL
        );
    }
}
