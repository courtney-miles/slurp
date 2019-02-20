<?php
/**
 * Author: Courtney Miles
 * Date: 22/09/18
 * Time: 7:01 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\SimpleDeleteStmt;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class SimpleDeleteStmtTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var \PDOStatement|MockInterface
     */
    protected $mockDelStmt;

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);
        $this->mockDelStmt = \Mockery::mock(\PDOStatement::class);
        $this->mockDelStmt->shouldReceive('execute')
            ->byDefault();
        $this->mockDelStmt->shouldReceive('rowCount')
            ->andReturn(0)
            ->byDefault();
        $this->mockPdo->shouldReceive('prepare')
            ->andReturn($this->mockDelStmt)
            ->byDefault();
    }

    public function testExecuteWithoutConditions()
    {
        $table = 'my_tbl';

        $expectedQry = <<<SQL
DELETE FROM `{$table}`
SQL;

        $this->mockPdo->shouldReceive('prepare')
            ->with($expectedQry)
            ->andReturn($this->mockDelStmt)
            ->once();
        $this->mockDelStmt->shouldReceive('execute')
            ->with([])
            ->once();

        $deleteStmt = new SimpleDeleteStmt($this->mockPdo, $table);
        $deleteStmt->execute();
    }

    public function testExecuteWithDatabase()
    {
        $database = 'my_db';
        $table = 'my_tbl';

        $expectedQry = <<<SQL
DELETE FROM `{$database}`.`{$table}`
SQL;

        $this->mockPdo->shouldReceive('prepare')
            ->with($expectedQry)
            ->andReturn($this->mockDelStmt)
            ->once();

        (new SimpleDeleteStmt($this->mockPdo, $table, [], $database))->execute();
    }

    public function testExecuteWithConditions()
    {
        $table = 'my_tbl';
        $conditions = ['col 1' => 123, 'col2' => 'abc'];

        $expectedQry = <<<SQL
DELETE FROM `{$table}` WHERE `col 1` = :col_1 AND `col2` = :col2
SQL;

        $this->mockPdo->shouldReceive('prepare')
            ->with($expectedQry)
            ->andReturn($this->mockDelStmt)
            ->once();
        $this->mockDelStmt->shouldReceive('execute')
            ->with([':col_1' => 123, ':col2' => 'abc'])
            ->once();

        (new SimpleDeleteStmt($this->mockPdo, $table, $conditions))->execute();
    }

    public function testExecuteReturnsAffectedRowCount()
    {
        $affectedRows = 3;

        $this->mockDelStmt->shouldReceive('rowCount')
            ->andReturn($affectedRows);

        $delete = new SimpleDeleteStmt($this->mockPdo, 'foo');

        $this->assertSame($affectedRows, $delete->execute());
    }
}
