<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:51 PM
 */

namespace MilesAsylum\Slurp\Stage;

class CommitTransactionStage implements OuterProcessStageInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(): void
    {
        $this->pdo->commit();
    }
}