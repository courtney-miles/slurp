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

use MilesAsylum\Slurp\Load\DatabaseLoader\QueryFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;
use MilesAsylum\Slurp\Load\Exception\MissingValueException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class BatchInsertManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var BatchInsertManager
     */
    protected $batchInsUpdStmt;

    /**
     * @var PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var QueryFactory|MockInterface
     */
    protected $mockQueryFactory;

    /**
     * @var string
     */
    protected $database = 'db_foo';

    /**
     * @var string
     */
    protected $table = 'tbl_bar';

    protected $columns = ['col_1', 'col_2'];

    public function setUp(): void
    {
        parent::setUp();

        $this->mockPdo = Mockery::mock(PDO::class);
        $this->mockQueryFactory = Mockery::mock(QueryFactory::class);

        $this->batchInsUpdStmt = new BatchInsertManager(
            $this->mockPdo,
            $this->table,
            $this->columns,
            $this->mockQueryFactory,
            $this->database
        );
    }

    public function testWriteOnce(): void
    {
        $rows = [
            ['col_1' => 123, 'col_2' => 234],
            ['col_1' => 345, 'col_2' => 456],
        ];
        $dummyQuery = '__INSERT_UPDATE__';

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->with($this->table, $this->columns, count($rows), $this->database)
            ->andReturn($dummyQuery);

        $mockStmt = Mockery::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->with($dummyQuery)
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')
            ->with([123, 234, 345, 456])
            ->once();

        $this->batchInsUpdStmt->write($rows);
    }

    public function testWriteTwice(): void
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
            ->with($this->table, $this->columns, count($rowsBatch1), $this->database)
            ->andReturn($dummyQuery)
            ->once();

        $mockStmt = Mockery::mock(PDOStatement::class);
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

    public function testColumnsOutOfOrder(): void
    {
        $rows = [
            ['col_1' => 123, 'col_2' => 234],
            ['col_2' => 345, 'col_1' => 456],
        ];

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = Mockery::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')
            ->with([123, 234, 456, 345])
            ->once();

        $this->batchInsUpdStmt->write($rows);
    }

    public function testSkipWriteIfRowsEmpty(): void
    {
        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = Mockery::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')->never();

        $this->batchInsUpdStmt->write([]);
    }

    public function testExceptionOnColumnMisMatch(): void
    {
        $this->expectException(MissingValueException::class);
        $this->expectExceptionMessage('Record 0 is missing values for the following fields: col_2.');

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = Mockery::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')->never();

        $this->batchInsUpdStmt->write(
            [['col_1' => 123]]
        );
    }

    public function testThrowRuntimeExceptionOnPDOException(): void
    {
        $pdoException = new \PDOException();

        $this->mockQueryFactory->shouldReceive('createInsertQuery')
            ->byDefault();

        $mockStmt = Mockery::mock(PDOStatement::class);
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($mockStmt);

        $mockStmt->shouldReceive('execute')
            ->andThrow($pdoException);

        try {
            $this->batchInsUpdStmt->write(
                [['col_1' => 123, 'col_2' => 234]]
            );
        } catch (\Exception $e) {
            $this->assertInstanceOf(LoadRuntimeException::class, $e);
            $this->assertSame(
                'PDO exception thrown when inserting batch of records into staging table.',
                $e->getMessage()
            );
            $this->assertSame($pdoException, $e->getPrevious());

            return;
        }

        $this->fail('Exception was not raised.');
    }
}
