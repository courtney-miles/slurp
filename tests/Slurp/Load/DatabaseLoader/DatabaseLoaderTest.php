<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 11:13 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchStmtInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdQueryFactory;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DatabaseLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var DatabaseLoader
     */
    protected $databaseLoader;

    /**
     * @var BatchStmtInterface|MockInterface
     */
    protected $mockBatchStmt;

    protected $batchSize = 3;

    public function setUp()
    {
        parent::setUp();

        $this->mockBatchStmt = \Mockery::mock(BatchStmtInterface::class);

        $this->databaseLoader = new DatabaseLoader(
            $this->mockBatchStmt,
            $this->batchSize
        );
    }

    public function testFlushSingleRow()
    {
        $values = ['col1' => 123, 'col2' => 234];

        $this->mockBatchStmt->shouldReceive('write')
            ->with([$values])
            ->once();

        $this->databaseLoader->loadValues($values);
        $this->databaseLoader->finalise();
    }

    public function testAutoFlushBatch()
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
            ['col1' => 567, 'col2' => 678],
        ];

        $this->mockBatchStmt->shouldReceive('write')
            ->with($rows)
            ->once();

        foreach ($rows as $row) {
            $this->databaseLoader->loadValues($row);
        }
    }
}
