<?php
/**
 * Author: Courtney Miles
 * Date: 21/09/18
 * Time: 7:18 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use PDO;

class SimpleDeleteStmt implements DmlStmtInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $conditions;

    /**
     * @var string
     */
    private $database;

    public function __construct(PDO $pdo, string $table, array $conditions = [], string $database = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->conditions = $conditions;
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): int
    {
        $conditionsStr = null;
        $qryParams = [];

        $tableRefTicked = "`{$this->table}`";

        if ($this->database !== null && $this->database !== '') {
            $tableRefTicked = "`{$this->database}`." . $tableRefTicked;
        }

        foreach ($this->conditions as $col => $value) {
            $valuePlaceholder = $this->colNameToPlaceholder($col);
            $conditions[] = sprintf(
                '`%s` = %s',
                $col,
                $valuePlaceholder
            );
            $qryParams[$valuePlaceholder] = $value;
        }

        if (!empty($conditions)) {
            $conditionsStr = 'WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare(trim("DELETE FROM {$tableRefTicked} {$conditionsStr}"));
        $stmt->execute($qryParams);

        return $stmt->rowCount();
    }

    protected function colNameToPlaceholder($colName): string
    {
        return ':' . str_replace([' '], ['_'], $colName);
    }
}
