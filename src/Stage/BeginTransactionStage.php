<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:49 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\Slurp;

class BeginTransactionStage implements OuterProcessStageInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Slurp $slurp): Slurp
    {
        $this->pdo->beginTransaction();

        return $slurp;
    }
}
