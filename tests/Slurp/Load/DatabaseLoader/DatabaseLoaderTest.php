<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 11:13 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchStmtInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DatabaseLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var BatchStmtInterface|MockInterface
     */
    protected $mockBatchStmt;

    protected $batchSize = 3;

    public function setUp()
    {
        parent::setUp();

        $this->mockBatchStmt = \Mockery::mock(BatchStmtInterface::class);
    }

    public function testAutoFlushBatch()
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
        ];

        $this->mockBatchStmt->shouldReceive('write')
            ->with($rows)
            ->once();

        $databaseLoader = new DatabaseLoader(
            $this->mockBatchStmt,
            2
        );

        foreach ($rows as $row) {
            $databaseLoader->loadValues($row);
        }
    }

    public function testFlushRemainingOnFinalise()
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
            ['col1' => 567, 'col2' => 678],
        ];

        $this->mockBatchStmt->shouldReceive('write')->byDefault();
        $this->mockBatchStmt->shouldReceive('write')
            ->with([$rows[2]])
            ->once();

        $databaseLoader = new DatabaseLoader(
            $this->mockBatchStmt,
            2
        );

        foreach ($rows as $row) {
            $databaseLoader->loadValues($row);
        }

        $databaseLoader->finalise();
    }


    public function testRemapColumns()
    {
        $row = ['col1' => 123, 'col2' => 234];

        $this->mockBatchStmt->shouldReceive('write')
            ->with([['col_one' => 123, 'col_two' => 234]])
            ->once();

        $databaseLoader = new DatabaseLoader(
            $this->mockBatchStmt,
            1,
            ['col_one' => 'col1', 'col_two' => 'col2']
        );

        $databaseLoader->loadValues($row);
    }
}
