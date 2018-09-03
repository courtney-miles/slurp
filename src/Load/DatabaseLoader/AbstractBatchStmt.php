<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 9:07 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\Exception\MissingValueException;

abstract class AbstractBatchStmt implements BatchStmtInterface
{
    /**
     * @var \PDO
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

    public function __construct(\PDO $pdo, string $table, array $columns)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->columns = $columns;
    }

    protected function ensureColumnMatch($rowId, array $rowValues): void
    {
        $missingFields = array_keys(
            array_diff_key(array_flip($this->columns), $rowValues)
        );

        if (count($missingFields)) {
            throw MissingValueException::createMissing($rowId, $missingFields);
        }
    }

    protected function convertRowCollectionToParams(array $rowCollection):array
    {
        $params = [];

        foreach ($rowCollection as $rowId => $row) {
            $this->ensureColumnMatch($rowId, $row);
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
