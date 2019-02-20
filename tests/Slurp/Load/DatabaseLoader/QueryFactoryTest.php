<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 7:42 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\QueryFactory;
use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    public function testCreateInsertQuery()
    {
        $queryFactory = new QueryFactory();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createInsertQuery('foo', ['col_alpha', 'col_beta'])
        );
    }

    public function testCreateInsertQueryWithDatabase()
    {
        $queryFactory = new QueryFactory();

        $expectedInsSql = <<<SQL
INSERT INTO `bar`.`foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createInsertQuery('foo', ['col_alpha', 'col_beta'], 1, 'bar')
        );
    }

    public function testCreateInsertQueryBatch()
    {
        $queryFactory = new QueryFactory();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?),
    (?, ?),
    (?, ?)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createInsertQuery('foo', ['col_alpha', 'col_beta'], 3)
        );
    }

    public function testExceptionOnNoColumns()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more columns must be supplied.');

        (new QueryFactory())->createInsertQuery('foo', []);
    }

    public function testExceptionOnBatchSizeLessThanOne()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The batch size cannot be less than 1.');

        (new QueryFactory())->createInsertQuery('foo', ['col_alpha'], 0);
    }
}
