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

use MilesAsylum\Slurp\Exception\LogicException;
use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;
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
     * @var string|null
     */
    private $database;

    protected $countRecordWritten = 0;

    /**
     * DatabaseLoader constructor.
     *
     * @param string $table
     * @param array $fieldMapping array key is the destination column and the array value is the source column
     * @param LoaderFactory $dmlFactory
     * @param int $batchSize
     * @param DmlStmtInterface|null $preCommitStmt
     * @param string|null $database
     */
    public function __construct(
        string $table,
        array $fieldMapping,
        LoaderFactory $dmlFactory,
        int $batchSize = 100,
        DmlStmtInterface $preCommitStmt = null,
        string $database = null
    ) {
        $this->loaderFactory = $dmlFactory;
        $this->table = $table;
        $this->batchSize = $batchSize;
        $this->fieldMapping = $fieldMapping;
        $this->preCommitStmt = $preCommitStmt;
        $this->database = $database;
    }

    /**
     * @param array $record
     *
     * @throws LogicException
     * @throws LoadRuntimeException thrown if an error occurs writing rows to the database
     */
    public function loadRecord(array $record): void
    {
        if (!$this->hasBegun()) {
            throw new LogicException(
                sprintf(
                    'Data cannot be loaded until %s has been called.',
                    __CLASS__ . '::begin()'
                )
            );
        }

        if ($this->isAborted()) {
            throw new LogicException('Data cannot be loaded because the loading has been aborted.');
        }

        $this->rowCollection[] = $this->mapColumnNames($record);

        if (count($this->rowCollection) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function begin(): void
    {
        $this->stagedLoad = $this->loaderFactory->createStagedLoad(
            $this->table,
            array_keys($this->fieldMapping),
            $this->database
        );
        $stagedTable = $this->stagedLoad->begin();
        $this->batchStmt = $this->loaderFactory->createBatchInsertManager(
            $stagedTable,
            array_keys($this->fieldMapping),
            $this->database
        );

        $this->begun = true;
    }

    public function hasBegun(): bool
    {
        return $this->begun;
    }

    /**
     * @throws LogicException
     */
    public function abort(): void
    {
        if (!$this->hasBegun()) {
            throw new LogicException('Unable to abort when loading has not begun.');
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
     * @throws LogicException
     * @throws LoadRuntimeException thrown if an database error occurs
     */
    public function finalise(): void
    {
        if (!$this->hasBegun()) {
            throw new LogicException('Unable to finalise when loading has not begun.');
        }

        if ($this->isAborted()) {
            throw new LogicException('Unable to finalise when loading has been aborted.');
        }

        $this->flush();

        if (null !== $this->preCommitStmt) {
            $this->preCommitStmt->execute();
        }

        $this->stagedLoad->commit();
    }

    /**
     * @throws LoadRuntimeException
     */
    protected function flush(): void
    {
        try {
            $this->batchStmt->write($this->rowCollection);
        } catch (LoadRuntimeException $e) {
            if ($e->getPrevious() instanceof \PDOException) {
                $e = new LoadRuntimeException(
                    sprintf(
                        'PDO exception thrown when batch inserting records %d through to %d: %s',
                        $this->getCountRecordsWritten() + 1,
                        $this->getCountRecordsWritten() + count($this->rowCollection),
                        $e->getPrevious()->getMessage()
                    ),
                    0,
                    $e->getPrevious()
                );
            }

            throw $e;
        }

        $this->addCountRecordsWritten(count($this->rowCollection));
        $this->rowCollection = [];
    }

    protected function mapColumnNames(array $row): array
    {
        if (empty($this->fieldMapping)) {
            return $row;
        }

        $newValues = [];

        foreach ($row as $sourceCol => $value) {
            foreach (array_keys($this->fieldMapping, $sourceCol, true) as $destCol) {
                $newValues[$destCol] = $value;
            }
        }

        return $newValues;
    }

    protected function addCountRecordsWritten(int $count): int
    {
        $this->countRecordWritten += $count;

        return $this->countRecordWritten;
    }

    protected function getCountRecordsWritten(): int
    {
        return $this->countRecordWritten;
    }
}
