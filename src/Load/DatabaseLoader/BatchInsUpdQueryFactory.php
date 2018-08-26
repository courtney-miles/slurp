<?php
/**
 * Author: Courtney Miles
 * Date: 17/08/18
 * Time: 6:39 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

class BatchInsUpdQueryFactory
{
    /**
     * @param $table
     * @param array $columns
     * @param int $batchSize
     * @return string
     * @throws \Exception
     */
    public function createQuery($table, array $columns, $batchSize = 1): string
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
        $updateValues = [];

        foreach ($columns as $column) {
            $updateValues[] = "`{$column}` = VALUES(`{$column}`)";
        }

        $updateValuesStr = implode(', ', $updateValues);

        return <<<SQL
INSERT INTO `{$table}` ({$colsStr})
  VALUES $batchValueStr
  ON DUPLICATE KEY UPDATE {$updateValuesStr}
SQL;
    }
}
