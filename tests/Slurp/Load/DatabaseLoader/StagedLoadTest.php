<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 7:41 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Exception\LogicException;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class StagedLoadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);
    }

    /**
     * @dataProvider getTableRefsForBeginCommitTest
     * @param string $table
     * @param string|null $database
     * @param string $tickedTableRef
     * @param string $tickedTempTableRef
     * @throws LogicException
     */
    public function testBeginThenCommit(
        string $table,
        ?string $database,
        string $tickedTableRef,
        string $tickedTempTableRef
    ): void {
        $columns = ['col_a', 'col_b'];
        $capturedSql = [];

        $expectedDropSql = <<<SQL
DROP TEMPORARY TABLE IF EXISTS {$tickedTempTableRef}
SQL;
        $expectedCreateSql = <<<SQL
CREATE TEMPORARY TABLE {$tickedTempTableRef} LIKE {$tickedTableRef}
SQL;
        $expectedInsertSql = <<<SQL
INSERT INTO {$tickedTableRef} (`col_a`, `col_b`)
  SELECT `col_a`, `col_b` FROM {$tickedTempTableRef}
  ON DUPLICATE KEY UPDATE `col_a` = VALUES(`col_a`), `col_b` = VALUES(`col_b`)
SQL;

        $this->mockPdo->shouldReceive('exec')
            ->withArgs(
                static function ($sql) use (&$capturedSql) {
                    $capturedSql[] = trim($sql);

                    return true;
                }
            );

        $stagedLoad = $this->createStagedLoad($this->mockPdo, $table, $columns, $database);

        $this->assertSame('_my_tbl_stage', $stagedLoad->begin());

        $this->assertSame(
            [$expectedDropSql, $expectedCreateSql],
            $capturedSql
        );

        $capturedSql = [];

        $stagedLoad->commit();

        $this->assertSame(
            [$expectedInsertSql, $expectedDropSql],
            $capturedSql
        );
    }

    public function getTableRefsForBeginCommitTest(): array
    {
        return [
            ['my_tbl', null, '`my_tbl`', '`_my_tbl_stage`'],
            ['my_tbl', 'my_db', '`my_db`.`my_tbl`', '`my_db`.`_my_tbl_stage`'],
        ];
    }

    public function testExceptionIfCommitWhenNotBegun(): void
    {
        $this->expectException(LogicException::class);
        $this->createStagedLoad($this->mockPdo, 'my_tbl', ['col_a'], null)->commit();
    }

    protected function createStagedLoad(\PDO $pdo, string $table, array $columns, string $database = null): StagedLoad
    {
        return new StagedLoad($pdo, $table, $columns, $database);
    }
}
