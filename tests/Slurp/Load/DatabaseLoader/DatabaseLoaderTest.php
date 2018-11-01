<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 11:13 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\DatabaseLoaderException;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\DmlStmtInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DatabaseLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var LoaderFactory|MockInterface
     */
    protected $mockLoaderFactory;

    /**
     * @var BatchInsertManager|MockInterface
     */
    protected $mockBatchStmt;

    /**
     * @var StagedLoad|MockInterface
     */
    protected $mockStagedLoad;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    protected $batchSize = 3;

    public function setUp()
    {
        parent::setUp();

        $this->mockBatchStmt = \Mockery::mock(BatchInsertManager::class);
        $this->mockStagedLoad = \Mockery::mock(StagedLoad::class);
        $this->mockStagedLoad->shouldReceive('begin')
            ->byDefault();
        $this->mockStagedLoad->shouldReceive('discard')
            ->byDefault();
        $this->mockLoaderFactory = \Mockery::mock(LoaderFactory::class);
        $this->mockLoaderFactory->shouldReceive('createBatchInsertManager')
            ->withAnyArgs()
            ->andReturn($this->mockBatchStmt)
            ->byDefault();
        $this->mockLoaderFactory->shouldReceive('createStagedLoad')
            ->withAnyArgs()
            ->andReturn($this->mockStagedLoad)
            ->byDefault();
    }

    public function testBegin()
    {
        $this->mockStagedLoad->shouldReceive('begin')
            ->once();

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        
        $this->assertTrue($databaseLoader->hasBegun());
    }

    public function testAbort()
    {
        $this->mockStagedLoad->shouldReceive('discard')
            ->once();

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);
        $databaseLoader->begin();

        $this->assertFalse($databaseLoader->isAborted());
        $databaseLoader->abort();
        $this->assertTrue($databaseLoader->isAborted());
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
            'my_tbl',
            ['col1' => 'col1', 'col2' => 'col2'],
            $this->mockLoaderFactory,
            2
        );
        $databaseLoader->begin();

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

        $this->mockStagedLoad->shouldReceive('commit')
            ->once();

        $databaseLoader = new DatabaseLoader(
            'my_tbl',
            ['col1' => 'col1', 'col2' => 'col2'],
            $this->mockLoaderFactory,
            2
        );
        $databaseLoader->begin();

        foreach ($rows as $row) {
            $databaseLoader->loadValues($row);
        }

        $databaseLoader->finalise();
    }

    public function testCallPreCommitDmlOnFinalise()
    {
        $mockPreCommitDml = \Mockery::mock(DmlStmtInterface::class);

        $mockPreCommitDml->shouldReceive('execute')
            ->once();

        $this->mockBatchStmt->shouldReceive('write')->byDefault();
        $this->mockStagedLoad->shouldReceive('commit')->byDefault();

        $databaseLoader = new DatabaseLoader(
            'my_tbl',
            ['col1' => 'col1', 'col2' => 'col2'],
            $this->mockLoaderFactory,
            2,
            $mockPreCommitDml
        );
        $databaseLoader->begin();

        $databaseLoader->finalise();
    }

    public function testRemapColumns()
    {
        $row = ['col1' => 123, 'col2' => 234];

        $this->mockBatchStmt->shouldReceive('write')
            ->with([['col_one' => 123, 'col_two' => 234]])
            ->once();

        $databaseLoader = new DatabaseLoader(
            'my_tbl',
            ['col_one' => 'col1', 'col_two' => 'col2'],
            $this->mockLoaderFactory,
            1
        );
        $databaseLoader->begin();

        $databaseLoader->loadValues($row);
    }

    public function testExceptionWhenLoadBeforeBegin()
    {
        $this->expectException(DatabaseLoaderException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->loadValues([]);
    }

    public function testExceptionWhenLoadAfterAbort()
    {
        $this->expectException(DatabaseLoaderException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        $databaseLoader->abort();
        $databaseLoader->loadValues([]);
    }

    public function testExceptionWhenAbortBeforeBegin()
    {
        $this->expectException(DatabaseLoaderException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->abort();
    }

    public function testExceptionWhenFinaliseBeforeBegin()
    {
        $this->expectException(DatabaseLoaderException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->finalise();
    }

    public function testExceptionWhenFinaliseAfterAbort()
    {
        $this->expectException(DatabaseLoaderException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        $databaseLoader->abort();

        $databaseLoader->finalise();
    }
}
