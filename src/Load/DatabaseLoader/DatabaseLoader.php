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

    /**
     * @var array
     */
    private $columnMapping;

    /**
     * DatabaseLoader constructor.
     * @param BatchStmtInterface $batchStmt
     * @param int $batchSize
     * @param array $columnMapping Array key is the destination column and the array value is the source column.
     */
    public function __construct(
        BatchStmtInterface $batchStmt,
        int $batchSize = 100,
        array $columnMapping = []
    ) {
        $this->batchSize = $batchSize;
        $this->batchStmt = $batchStmt;
        $this->columnMapping = $columnMapping;
    }

    public function loadValues(array $values): void
    {
        $this->rowCollection[] = $this->mapColumnNames($values);

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

    protected function mapColumnNames(array $values): array
    {
        if (empty($this->columnMapping)) {
            return $values;
        }

        $newValues = [];

        foreach ($values as $sourceCol => $value) {
            foreach (array_keys($this->columnMapping, $sourceCol) as $destCol) {
                $newValues[$destCol] = $value;
            }
        }

        return $newValues;
    }
}
