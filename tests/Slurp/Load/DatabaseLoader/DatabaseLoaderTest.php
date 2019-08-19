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

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Exception\LogicException;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\DmlStmtInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PDO;
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
    protected $mockBatchInsertManager;

    /**
     * @var StagedLoad|MockInterface
     */
    protected $mockStagedLoad;

    /**
     * @var PDO|MockInterface
     */
    protected $mockPdo;

    protected $batchSize = 3;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockBatchInsertManager = $this->createMockBatchInsertManager();
        $this->mockStagedLoad = $this->createMockStagedLoad();
        $this->mockLoaderFactory = $this->createMockLoaderFactory(
            $this->mockBatchInsertManager,
            $this->mockStagedLoad
        );
    }

    public function testBegin(): void
    {
        $this->mockStagedLoad->shouldReceive('begin')
            ->once();

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        
        $this->assertTrue($databaseLoader->hasBegun());
    }

    public function testAbort(): void
    {
        $this->mockStagedLoad->shouldReceive('discard')
            ->once();

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);
        $databaseLoader->begin();

        $this->assertFalse($databaseLoader->isAborted());
        $databaseLoader->abort();
        $this->assertTrue($databaseLoader->isAborted());
    }

    public function testAutoFlushBatch(): void
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
        ];

        $this->mockBatchInsertManager->shouldReceive('write')
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
            $databaseLoader->loadRecord($row);
        }
    }

    public function testFlushRemainingOnFinalise(): void
    {
        $rows = [
            ['col1' => 123, 'col2' => 234],
            ['col1' => 345, 'col2' => 456],
            ['col1' => 567, 'col2' => 678],
        ];

        $this->mockBatchInsertManager->shouldReceive('write')->byDefault();
        $this->mockBatchInsertManager->shouldReceive('write')
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
            $databaseLoader->loadRecord($row);
        }

        $databaseLoader->finalise();
    }

    public function testRethrowLoadRuntimeException(): void
    {
        $row = ['col1' => 123, 'col2' => 234];
        $pdoExceptionMsg = 'SQL Fubar';
        $pdoException = new \PDOException($pdoExceptionMsg);
        $origException = new LoadRuntimeException('', 0, $pdoException);

        $this->mockBatchInsertManager->shouldReceive('write')
            ->andThrow($origException);

        $databaseLoader = new DatabaseLoader(
            'my_tbl',
            ['col1' => 'col1', 'col2' => 'col2'],
            $this->mockLoaderFactory,
            1
        );
        $databaseLoader->begin();

        try {
            $databaseLoader->loadRecord($row);
        } catch (\Exception $e) {
            $this->assertNotSame($origException, $e);
            $this->assertInstanceOf(LoadRuntimeException::class, $e);
            $this->assertSame(
                'PDO exception thrown when batch inserting records 1 through to 1: ' . $pdoExceptionMsg,
                $e->getMessage()
            );
            $this->assertSame($pdoException, $e->getPrevious());
            return;
        }

        $this->fail('Exception was not raised.');
    }

    public function testCallPreCommitDmlOnFinalise(): void
    {
        $mockPreCommitDml = Mockery::mock(DmlStmtInterface::class);

        $mockPreCommitDml->shouldReceive('execute')
            ->once();

        $this->mockBatchInsertManager->shouldReceive('write')->byDefault();
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

    public function testRemapColumns(): void
    {
        $row = ['col1' => 123, 'col2' => 234];

        $this->mockBatchInsertManager->shouldReceive('write')
            ->with([['col_one' => 123, 'col_two' => 234]])
            ->once();

        $databaseLoader = new DatabaseLoader(
            'my_tbl',
            ['col_one' => 'col1', 'col_two' => 'col2'],
            $this->mockLoaderFactory,
            1
        );
        $databaseLoader->begin();

        $databaseLoader->loadRecord($row);
    }

    public function testCreateBatchInsertManager(): void
    {
        $table = '_tmp_tbl_foo';
        $fieldMapping = ['col_a' => []];
        $database = 'db_bar';

        $this->mockLoaderFactory->shouldReceive('createBatchInsertManager')
            ->with($table, array_keys($fieldMapping), $database)
            ->andReturn($this->mockBatchInsertManager)
            ->once();
        $this->mockStagedLoad->shouldReceive('begin')
            ->andReturn($table);

        $databaseLoader = new DatabaseLoader(
            'tbl_foo',
            $fieldMapping,
            $this->mockLoaderFactory,
            1,
            null,
            $database
        );
        $databaseLoader->begin();
    }

    public function testExceptionWhenLoadBeforeBegin(): void
    {
        $this->expectException(LogicException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->loadRecord([]);
    }

    public function testExceptionWhenLoadAfterAbort(): void
    {
        $this->expectException(LogicException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        $databaseLoader->abort();
        $databaseLoader->loadRecord([]);
    }

    public function testExceptionWhenAbortBeforeBegin(): void
    {
        $this->expectException(LogicException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->abort();
    }

    public function testExceptionWhenFinaliseBeforeBegin(): void
    {
        $this->expectException(LogicException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->finalise();
    }

    public function testExceptionWhenFinaliseAfterAbort(): void
    {
        $this->expectException(LogicException::class);

        $databaseLoader = new DatabaseLoader('', [], $this->mockLoaderFactory, 1);

        $databaseLoader->begin();
        $databaseLoader->abort();

        $databaseLoader->finalise();
    }

    /**
     * @param BatchInsertManager $batchInsertManager
     * @param StagedLoad $stagedLoad
     * @return LoaderFactory|MockInterface
     */
    protected function createMockLoaderFactory(
        BatchInsertManager $batchInsertManager,
        StagedLoad $stagedLoad
    ): MockInterface {
        $mockLoaderFactory = Mockery::mock(LoaderFactory::class);
        $mockLoaderFactory->shouldReceive('createBatchInsertManager')
            ->withAnyArgs()
            ->andReturn($batchInsertManager)
            ->byDefault();
        $mockLoaderFactory->shouldReceive('createStagedLoad')
            ->withAnyArgs()
            ->andReturn($stagedLoad)
            ->byDefault();

        return $mockLoaderFactory;
    }

    /**
     * @return StagedLoad|MockInterface
     */
    protected function createMockStagedLoad(): MockInterface
    {
        $mockStagedLoad = Mockery::mock(StagedLoad::class);
        $mockStagedLoad->shouldReceive('begin')
            ->byDefault();
        $mockStagedLoad->shouldReceive('discard')
            ->byDefault();

        return $mockStagedLoad;
    }

    /**
     * @return BatchInsertManager|MockInterface
     */
    protected function createMockBatchInsertManager(): MockInterface
    {
        $mockBatchInsertManager = Mockery::mock(BatchInsertManager::class);

        return $mockBatchInsertManager;
    }
}
