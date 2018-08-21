<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 8:50 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchWriter;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\InsertUpdateSql;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class DatabaseLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var DatabaseLoader
     */
    protected $loader;

    /**
     * @var LoaderFactory|MockInterface
     */
    protected $mockLoaderFactory;

    /**
     * @var InsertUpdateSql|MockInterface
     */
    protected $mockQueryFactory;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    public function setUp()
    {
        parent::setUp();

        $this->mockQueryFactory = \Mockery::mock(InsertUpdateSql::class);
        $this->mockPdo = \Mockery::mock(\PDO::class);
        $this->mockLoaderFactory = \Mockery::mock(LoaderFactory::class);

        $this->mockPdo->shouldReceive('inTransaction')
            ->andReturn(true)
            ->byDefault();
        $this->mockPdo->shouldReceive('commit')
            ->byDefault();

        $this->loader = new DatabaseLoader(
            $this->mockPdo,
            $this->mockLoaderFactory,
            $this->mockQueryFactory
        );
    }

    public function testLoadRow()
    {
        $table = 'foo';
        $columns = ['col1', 'col2'];

        $row = ['col1' => 123, 'col2' => 234];

        $mockBatchWriter = \Mockery::mock(BatchWriter::class);

        $this->stubCreateBatchDatabaseInsert($this->mockLoaderFactory, $table, $columns, $mockBatchWriter);

        $mockBatchWriter->shouldReceive('addRowValues')
            ->with($row)
            ->once();

        $this->loader->addDestinationTable($table, $columns);
        $this->loader->loadRow($row);
    }

    public function testLoadMultiTable()
    {
        $tableFoo = 'foo';
        $columnsFoo = ['col1', 'col2'];
        $tableBar = 'bar';
        $columnsBar = ['col0', 'col3'];

        $row = ['col0' => 012, 'col1' => 123, 'col2' => 234, 'col3' => 345];

        $mockFooBatchWriter = \Mockery::mock(BatchWriter::class);
        $mockBarBatchWriter = \Mockery::mock(BatchWriter::class);

        $this->stubCreateBatchDatabaseInsert($this->mockLoaderFactory, $tableFoo, $columnsFoo, $mockFooBatchWriter);
        $this->stubCreateBatchDatabaseInsert($this->mockLoaderFactory, $tableBar, $columnsBar, $mockBarBatchWriter);

        $mockFooBatchWriter->shouldReceive('addRowValues')
            ->with(['col1' => $row['col1'], 'col2' => $row['col2']])
            ->once();

        $mockBarBatchWriter->shouldReceive('addRowValues')
            ->with(['col0' => $row['col0'], 'col3' => $row['col3']])
            ->once();

        $this->loader->addDestinationTable($tableFoo, $columnsFoo);
        $this->loader->addDestinationTable($tableBar, $columnsBar);
        $this->loader->loadRow($row);
    }

    public function testFlushOnFinalise()
    {
        $table = 'foo';
        $columns = ['col1', 'col2'];

        $mockBatchWriter = \Mockery::mock(BatchWriter::class);

        $this->stubCreateBatchDatabaseInsert($this->mockLoaderFactory, $table, $columns, $mockBatchWriter);

        $mockBatchWriter->shouldReceive('flush')
            ->once();

        $this->loader->addDestinationTable($table, $columns);
        $this->loader->finalise();
    }

    public function testStartTransactionOnce()
    {
        $this->mockPdo->shouldReceive('inTransaction')
            ->andReturn(false, true);
        $this->mockPdo->shouldReceive('beginTransaction')
            ->once();

        $this->loader->loadRow(['foo', 'bar']);
        $this->loader->loadRow(['foo', 'bar']);
    }

    public function testCommitTransactionOnFinalise()
    {
        $this->mockPdo->shouldReceive('inTransaction')
            ->andReturn(true);
        $this->mockPdo->shouldReceive('commit')
            ->once();

        $this->loader->finalise();
    }

    protected function stubCreateBatchDatabaseInsert(
        MockInterface $mockLoaderFactory,
        $table,
        array $columns,
        BatchWriter $batchWriter
    ) {
        $mockLoaderFactory->shouldReceive('createBatchDatabaseInsert')
            ->with($this->mockPdo, $this->mockQueryFactory, $table, $columns)
            ->andReturn($batchWriter);
    }
}
