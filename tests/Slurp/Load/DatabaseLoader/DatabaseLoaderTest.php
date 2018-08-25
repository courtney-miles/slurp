<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 11:13 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\InsertUpdateSql;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DatabaseLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var InsertUpdateSql|MockInterface
     */
    protected $mockQueryFactory;

    /**
     * @var \PDOStatement|MockInterface
     */
    protected $mockBatchStmt;

    /**
     * @var \PDOStatement|MockInterface
     */
    protected $mockSingleStmt;

    /**
     * @var DatabaseLoader
     */
    protected $databaseLoader;

    public function setUp()
    {
        parent::setUp();

        $table = 'foo';
        $columns = ['col1', 'col2'];
        $batchSize = 3;
        $singleInsQuery = '__INSERT__';
        $batchInsQuery = '__BATCH_INSERT__';

        $this->mockPdo = \Mockery::mock(\PDO::class);
        $this->mockQueryFactory = \Mockery::mock(InsertUpdateSql::class);
        $this->mockBatchStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockSingleStmt = \Mockery::mock(\PDOStatement::class);

        $this->mockQueryFactory->shouldReceive('createSql')
            ->with($table, $columns)
            ->andReturn($singleInsQuery);
        $this->mockQueryFactory->shouldReceive('createSql')
            ->with($table, $columns, $batchSize)
            ->andReturn($batchInsQuery);

        $this->mockPdo->shouldReceive('prepare')
            ->with($singleInsQuery)
            ->andReturn($this->mockSingleStmt);
        $this->mockPdo->shouldReceive('prepare')
            ->with($batchInsQuery)
            ->andReturn($this->mockBatchStmt);

        $this->databaseLoader = new DatabaseLoader(
            $this->mockPdo,
            $this->mockQueryFactory,
            $table,
            $columns,
            $batchSize
        );
    }

    public function testFlushSingleRow()
    {
        $row = ['col1' => 123, 'col2' => 234];

        $this->mockSingleStmt->shouldReceive('execute')
            ->with(array_values($row))
            ->once();

        $this->databaseLoader->loadValues($row);
        $this->databaseLoader->finalise();
    }

    public function testAutoFlushBatch()
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
            ['col1' => 567, 'col2' => 678],
        ];

        $this->mockBatchStmt->shouldReceive('execute')
            ->with([123,234,345,456,567,678])
            ->once();

        foreach ($rows as $row) {
            $this->databaseLoader->loadValues($row);
        }
    }
}
