<?php
/**
 * Author: Courtney Miles
 * Date: 17/08/18
 * Time: 6:39 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

class QueryFactory
{
    /**
     * @param string $table
     * @param array $columns
     * @param int $batchSize
     * @return string
     */
    public function createInsertQuery(string $table, array $columns, int $batchSize = 1): string
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('One or more columns must be supplied.');
        }

        if ($batchSize < 1) {
            throw new \InvalidArgumentException('The batch size cannot be less than 1.');
        }

        $colsStr = '`' . implode('`, `', $columns) . '`';
        $valueStr = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $batchValueStr = implode(",\n    ", array_fill(0, $batchSize, $valueStr));

        return <<<SQL
INSERT INTO `{$table}` ({$colsStr})
  VALUES $batchValueStr
SQL;
    }
}
