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

        if (null !== $this->database && '' !== $this->database) {
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

        try {
            $stmt = $this->pdo->prepare(trim("DELETE FROM {$tableRefTicked} {$conditionsStr}"));
            $stmt->execute($qryParams);
        } catch (\PDOException $e) {
            throw new LoadRuntimeException(
                'PDO exception thrown when deleting rows.',
                0,
                $e
            );
        }

        return $stmt->rowCount();
    }

    protected function colNameToPlaceholder($colName): string
    {
        return ':' . str_replace([' '], ['_'], $colName);
    }
}
