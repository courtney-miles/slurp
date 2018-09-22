<?php
/**
 * Author: Courtney Miles
 * Date: 21/09/18
 * Time: 7:18 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;


class PreCommitSimpleDelete implements PreCommitDmlInterface
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
     * @var array
     */
    private $conditions;

    public function __construct(\PDO $pdo, string $table, array $conditions = [])
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->conditions = $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): int
    {
        $conditionsStr = null;
        $qryParams = [];

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
            $conditionsStr = ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare("DELETE FROM `{$this->table}`$conditionsStr");
        $stmt->execute($qryParams);

        return $stmt->rowCount();
    }

    protected function colNameToPlaceholder($colName)
    {
        return ':' . str_replace([' '], ['_'], $colName);
    }
}
