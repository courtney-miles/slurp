<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 9:22 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\QueryFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\Exception\MissingValueException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class BatchInsertManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var BatchInsertManager
     */
    protected $batchInsUpdStmt;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var QueryFactory|MockInterface
     */
    protected $mockQueryFactory;

    protected $table = 'tbl_foo';

    protected $columns = ['col_1', 'col_2'];

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);
        $this->mockQueryFactory = \Mockery::mock(QueryFactory::class);

        $this->batchInsUpdStmt = new BatchInsertManager(
            $this->mockPdo,
            $this->table,
            $this->columns,
            $this->mockQueryFactory
        );
    }

    public function testWriteOnce()
    {
        $rows = [
            ['col_1' => 123, 'col_2' => 234],
            ['col_1' => 345, 'col_2' => 456],
        ];
        $dummyQuery = '__INSERT_UPDATE__';

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->with($this->table, $this->columns, count($rows))
            ->andReturn($dummyQuery);

        $mockStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->with($dummyQuery)
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')
            ->with([123, 234, 345, 456])
            ->once();

        $this->batchInsUpdStmt->write($rows);
    }

    public function testWriteTwice()
    {
        $rowsBatch1 = [
            ['col_1' => 123, 'col_2' => 234],
            ['col_1' => 345, 'col_2' => 456],
        ];
        $rowsBatch2 = [
            ['col_1' => 567, 'col_2' => 678],
            ['col_1' => 789, 'col_2' => 890],
        ];
        $dummyQuery = '__INSERT_UPDATE__';

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->with($this->table, $this->columns, count($rowsBatch1))
            ->andReturn($dummyQuery)
            ->once();

        $mockStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->with($dummyQuery)
            ->andReturn($mockStmt)
            ->once();

        $mockStmt->shouldReceive('execute')
            ->with([123, 234, 345, 456])
            ->once();
        $mockStmt->shouldReceive('execute')
            ->with([567, 678, 789, 890])
            ->once();

        $this->batchInsUpdStmt->write($rowsBatch1);
        $this->batchInsUpdStmt->write($rowsBatch2);
    }

    public function testColumnsOutOfOrder()
    {
        $rows = [
            ['col_1' => 123, 'col_2' => 234],
            ['col_2' => 345, 'col_1' => 456],
        ];

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')
            ->with([123, 234, 456, 345])
            ->once();

        $this->batchInsUpdStmt->write($rows);
    }

    public function testSkipWriteIfRowsEmpty()
    {
        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')->never();

        $this->batchInsUpdStmt->write([]);
    }

    public function testExceptionOnColumnMisMatch()
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Record 0 is missing values for the following fields: col_2.');

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')->never();

        $this->batchInsUpdStmt->write(
            [['col_1' => 123]]
        );
    }
}
