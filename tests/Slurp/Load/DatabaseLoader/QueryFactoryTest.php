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

use InvalidArgumentException;
use MilesAsylum\Slurp\Load\DatabaseLoader\QueryFactory;
use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    public function testCreateInsertQuery(): void
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

    public function testCreateInsertQueryWithDatabase(): void
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

    public function testCreateInsertQueryBatch(): void
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

    public function testExceptionOnNoColumns(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more columns must be supplied.');

        (new QueryFactory())->createInsertQuery('foo', []);
    }

    public function testExceptionOnBatchSizeLessThanOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The batch size cannot be less than 1.');

        (new QueryFactory())->createInsertQuery('foo', ['col_alpha'], 0);
    }
}
