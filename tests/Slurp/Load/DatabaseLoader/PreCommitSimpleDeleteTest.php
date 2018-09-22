<?php
/**
 * Author: Courtney Miles
 * Date: 22/09/18
 * Time: 7:01 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitSimpleDelete;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class PreCommitSimpleDeleteTest extends TestCase
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
    }

    public function testExecuteWithoutConditions()
    {
        $affectedRows = 3;
        $table = 'my_tbl';

        $expectedQry = <<<SQL
DELETE FROM `my_tbl`
SQL;

        $this->mockPdo->shouldReceive('prepare')
            ->with($expectedQry)
            ->andReturn($this->mockDelStmt)
            ->once();
        $this->mockDelStmt->shouldReceive('execute')
            ->with([])
            ->once();
        $this->mockDelStmt->shouldReceive('rowCount')
            ->andReturn($affectedRows);

        $delete = new PreCommitSimpleDelete($this->mockPdo, $table);

        $this->assertSame($affectedRows, $delete->execute());
    }

    public function testExecuteWithConditions()
    {
        $affectedRows = 3;
        $table = 'my_tbl';
        $conditions = ['col 1' => 123, 'col2' => 'abc'];

        $expectedQry = <<<SQL
DELETE FROM `my_tbl` WHERE `col 1` = :col_1 AND `col2` = :col2
SQL;

        $this->mockPdo->shouldReceive('prepare')
            ->with($expectedQry)
            ->andReturn($this->mockDelStmt)
            ->once();
        $this->mockDelStmt->shouldReceive('execute')
            ->with([':col_1' => 123, ':col2' => 'abc'])
            ->once();
        $this->mockDelStmt->shouldReceive('rowCount')
            ->andReturn($affectedRows);

        $delete = new PreCommitSimpleDelete($this->mockPdo, $table, $conditions);

        $this->assertSame($affectedRows, $delete->execute());
    }
}
