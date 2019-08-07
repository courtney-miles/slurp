<?php
/**
 * Author: Courtney Miles
 * Date: 17/08/18
 * Time: 6:39 AM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use InvalidArgumentException;

class QueryFactory
{
    /**
     * @param string $table
     * @param array $columns
     * @param int $batchSize
     * @param string $database
     * @return string
     */
    public function createInsertQuery(
        string $table,
        array $columns,
        int $batchSize = 1,
        string $database = null
    ): string {
        if (empty($columns)) {
            throw new InvalidArgumentException('One or more columns must be supplied.');
        }

        if ($batchSize < 1) {
            throw new InvalidArgumentException('The batch size cannot be less than 1.');
        }

        $tableRefTicked = "`{$table}`";

        if ($database !== null && $database !== '') {
            $tableRefTicked = "`{$database}`." . $tableRefTicked;
        }

        $colsStr = '`' . implode('`, `', $columns) . '`';
        $valueStr = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $batchValueStr = implode(",\n    ", array_fill(0, $batchSize, $valueStr));

        return <<<SQL
INSERT INTO {$tableRefTicked} ({$colsStr})
  VALUES $batchValueStr
SQL;
    }
}
