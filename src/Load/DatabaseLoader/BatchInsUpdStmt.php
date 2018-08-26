<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 8:53 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

class BatchInsUpdStmt extends AbstractBatchStmt
{
    /**
     * @var BatchInsUpdQueryFactory
     */
    private $queryFactory;

    /**
     * @var \PDOStatement[]
     */
    private $preparedBatchStmts = [];

    public function __construct(\PDO $pdo, string $table, array $columns, BatchInsUpdQueryFactory $queryFactory)
    {
        parent::__construct($pdo, $table, $columns);
        $this->queryFactory = $queryFactory;
    }

    /**
     * @param array[] $rows
     */
    public function write(array $rows)
    {
        if (!empty($rows)) {
            $this->getPreparedBatchStmt(count($rows))
                ->execute(
                    $this->convertRowCollectionToParams($rows)
                );
        }
    }

    protected function getPreparedBatchStmt($count): \PDOStatement
    {
        if (!isset($this->preparedBatchStmts[$count])) {
            $this->preparedBatchStmts[$count] = $this->pdo->prepare(
                $this->queryFactory->createQuery(
                    $this->table,
                    $this->columns,
                    $count
                )
            );
        }

        return $this->preparedBatchStmts[$count];
    }
}
