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

class QueryFactory
{
    public function createInsertQuery(
        string $table,
        array $columns,
        int $batchSize = 1,
        ?string $database = null
    ): string {
        if (empty($columns)) {
            throw new \InvalidArgumentException('One or more columns must be supplied.');
        }

        if ($batchSize < 1) {
            throw new \InvalidArgumentException('The batch size cannot be less than 1.');
        }

        $tableRefTicked = "`{$table}`";

        if (null !== $database && '' !== $database) {
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
