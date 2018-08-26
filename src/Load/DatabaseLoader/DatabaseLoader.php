<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 10:25 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\LoaderInterface;

class DatabaseLoader implements LoaderInterface
{
    /**
     * @var \PDOStatement
     */
    protected $batchStmt;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @var array[]
     */
    protected $rowCollection = [];

    public function __construct(
        BatchStmtInterface $batchStmt,
        int $batchSize = 100
    ) {
        $this->batchSize = $batchSize;
        $this->batchStmt = $batchStmt;
    }

    public function loadValues(array $values): void
    {
        $this->rowCollection[] = $values;

        if (count($this->rowCollection) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function finalise(): void
    {
        $this->flush();
    }

    protected function flush(): void
    {
        $this->batchStmt->write($this->rowCollection);
        $this->rowCollection = [];
    }
}
