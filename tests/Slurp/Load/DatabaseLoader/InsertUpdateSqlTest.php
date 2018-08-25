<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 7:42 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\InsertUpdateSql;
use PHPUnit\Framework\TestCase;

class InsertUpdateSqlTest extends TestCase
{
    public function testCreateInsertQuery()
    {
        $queryFactory = new InsertUpdateSql();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?)
  ON DUPLICATE KEY UPDATE `col_alpha` = VALUES(`col_alpha`), `col_beta` = VALUES(`col_beta`)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createSql('foo', ['col_alpha', 'col_beta'])
        );
    }

    public function testCreateInsertQueryBatch()
    {
        $queryFactory = new InsertUpdateSql();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?),
    (?, ?),
    (?, ?)
  ON DUPLICATE KEY UPDATE `col_alpha` = VALUES(`col_alpha`), `col_beta` = VALUES(`col_beta`)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createSql('foo', ['col_alpha', 'col_beta'], 3)
        );
    }
}
