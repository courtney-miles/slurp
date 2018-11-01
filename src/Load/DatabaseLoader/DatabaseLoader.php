<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 10:25 PM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\DatabaseLoaderException;
use MilesAsylum\Slurp\Load\LoaderInterface;

class DatabaseLoader implements LoaderInterface
{
    /**
     * @var BatchManagerInterface
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
    private $fieldMapping;
    /**
     * @var string
     */
    private $table;

    /**
     * @var StagedLoad
     */
    private $stagedLoad;

    /**
     * @var LoaderFactory
     */
    private $loaderFactory;

    protected $begun = false;

    protected $aborted = false;
    /**
     * @var DmlStmtInterface
     */
    private $preCommitStmt;

    /**
     * DatabaseLoader constructor.
     * @param string $table
     * @param array $fieldMapping Array key is the destination column and the array value is the source column.
     * @param LoaderFactory $dmlFactory
     * @param int $batchSize
     * @param DmlStmtInterface|null $preCommitStmt
     */
    public function __construct(
        string $table,
        array $fieldMapping,
        LoaderFactory $dmlFactory,
        int $batchSize = 100,
        DmlStmtInterface $preCommitStmt = null
    ) {
        $this->loaderFactory = $dmlFactory;
        $this->table = $table;
        $this->batchSize = $batchSize;
        $this->fieldMapping = $fieldMapping;
        $this->preCommitStmt = $preCommitStmt;
    }

    /**
     * @param array $values
     * @throws DatabaseLoaderException
     */
    public function loadValues(array $values): void
    {
        if (!$this->hasBegun()) {
            throw new DatabaseLoaderException(
                sprintf(
                    'Data cannot be loaded until %s has been called.',
                    __CLASS__ . '::begin()'
                )
            );
        }

        if ($this->isAborted()) {
            throw new DatabaseLoaderException('Data cannot be loaded because the loading has been aborted.');
        }

        $this->rowCollection[] = $this->mapColumnNames($values);

        if (count($this->rowCollection) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function begin(): void
    {
        $this->stagedLoad = $this->loaderFactory->createStagedLoad(
            $this->table,
            array_keys($this->fieldMapping)
        );
        $stagedTable = $this->stagedLoad->begin();
        $this->batchStmt = $this->loaderFactory->createBatchInsertManager(
            $stagedTable,
            array_keys($this->fieldMapping)
        );

        $this->begun = true;
    }

    public function hasBegun(): bool
    {
        return $this->begun;
    }

    /**
     * @throws DatabaseLoaderException
     */
    public function abort(): void
    {
        if (!$this->hasBegun()) {
            throw new DatabaseLoaderException('Unable to abort when loading has not begun.');
        }

        $this->aborted = true;
        $this->stagedLoad->discard();
        $this->stagedLoad = null;
        $this->batchStmt = null;
    }

    public function isAborted(): bool
    {
        return $this->aborted;
    }

    /**
     * @throws DatabaseLoaderException
     */
    public function finalise(): void
    {
        if (!$this->hasBegun()) {
            throw new DatabaseLoaderException('Unable to finalise when loading has not begun.');
        }

        if ($this->isAborted()) {
            throw new DatabaseLoaderException('Unable to finalise when loading has been aborted.');
        }

        $this->flush();

        if ($this->preCommitStmt !== null) {
            $this->preCommitStmt->execute();
        }

        $this->stagedLoad->commit();
    }

    protected function flush(): void
    {
        $this->batchStmt->write($this->rowCollection);
        $this->rowCollection = [];
    }

    protected function mapColumnNames(array $values): array
    {
        if (empty($this->fieldMapping)) {
            return $values;
        }

        $newValues = [];

        foreach ($values as $sourceCol => $value) {
            foreach (array_keys($this->fieldMapping, $sourceCol) as $destCol) {
                $newValues[$destCol] = $value;
            }
        }

        return $newValues;
    }
}
