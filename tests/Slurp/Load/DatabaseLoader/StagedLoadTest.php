<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 7:41 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\Exception\DatabaseLoaderException;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class StagedLoadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var StagedLoad
     */
    protected $stagedLoad;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    protected $table = 'my_tbl';

    protected $columns = ['col_a', 'col_b'];

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);

        $this->stagedLoad = new StagedLoad(
            $this->mockPdo,
            $this->table,
            $this->columns
        );
    }

    public function testBeginThenCommit()
    {
        $capturedSql = [];

        $expectedDropSql = <<<SQL
DROP TEMPORARY TABLE IF EXISTS `_my_tbl_stage`
SQL;
        $expectedCreateSql = <<<SQL
CREATE TEMPORARY TABLE `_my_tbl_stage` LIKE `my_tbl`
SQL;
        $expectedInsertSql = <<<SQL
INSERT INTO `my_tbl` (`col_a`, `col_b`)
  SELECT `col_a`, `col_b` FROM `_my_tbl_stage`
  ON DUPLICATE KEY UPDATE `col_a` = VALUES(`col_a`), `col_b` = VALUES(`col_b`)
SQL;

        $this->mockPdo->shouldReceive('exec')
            ->withArgs(
                function ($sql) use (&$capturedSql) {
                    $capturedSql[] = $sql;

                    return true;
                }
            );

        $this->assertSame('_my_tbl_stage', $this->stagedLoad->begin());

        $this->assertSame(
            [$expectedDropSql, $expectedCreateSql],
            $capturedSql
        );

        $capturedSql = [];

        $this->stagedLoad->commit();

        $this->assertSame(
            [$expectedInsertSql, $expectedDropSql],
            $capturedSql
        );
    }

    public function testExceptionIfCommitWhenNotBegun()
    {
        $this->expectException(DatabaseLoaderException::class);
        $this->stagedLoad->commit();
    }
}
