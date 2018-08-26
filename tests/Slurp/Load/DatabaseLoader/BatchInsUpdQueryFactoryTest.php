<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 7:42 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsUpdQueryFactory;
use PHPUnit\Framework\TestCase;

class BatchInsUpdQueryFactoryTest extends TestCase
{
    public function testCreateInsertQuery()
    {
        $queryFactory = new BatchInsUpdQueryFactory();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?)
  ON DUPLICATE KEY UPDATE `col_alpha` = VALUES(`col_alpha`), `col_beta` = VALUES(`col_beta`)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createQuery('foo', ['col_alpha', 'col_beta'])
        );
    }

    public function testCreateInsertQueryBatch()
    {
        $queryFactory = new BatchInsUpdQueryFactory();

        $expectedInsSql = <<<SQL
INSERT INTO `foo` (`col_alpha`, `col_beta`)
  VALUES (?, ?),
    (?, ?),
    (?, ?)
  ON DUPLICATE KEY UPDATE `col_alpha` = VALUES(`col_alpha`), `col_beta` = VALUES(`col_beta`)
SQL;
        $this->assertSame(
            $expectedInsSql,
            $queryFactory->createQuery('foo', ['col_alpha', 'col_beta'], 3)
        );
    }

    public function testExceptionOnNoColumns()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more columns must be supplied.');

        (new BatchInsUpdQueryFactory())->createQuery('foo', []);
    }

    public function testExceptionOnBatchSizeLessThanOne()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The batch size cannot be less than 1.');

        (new BatchInsUpdQueryFactory())->createQuery('foo', ['col_alpha'], 0);
    }
}
