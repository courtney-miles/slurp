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

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\DatabaseExtractor;

use Iterator;
use MilesAsylum\Slurp\Extract\DatabaseExtractor\DatabaseExtractor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class DatabaseExtractorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var PDOStatement|MockInterface
     */
    protected $mockStmt;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockPdo = Mockery::mock(PDO::class);
        $this->mockStmt = Mockery::mock(PDOStatement::class);
    }

    public function testGetIterator(): void
    {
        $qry = 'SELECT';
        $qryParams = [':foo' => 123];

        $this->mockPdo->shouldReceive('prepare')
            ->with($qry)
            ->andReturn($this->mockStmt);
        $this->mockStmt->shouldReceive('execute')
            ->with($qryParams);

        $dbExtractor = new DatabaseExtractor(
            $this->mockPdo,
            $qry,
            $qryParams
        );

        $this->assertInstanceOf(Iterator::class, $dbExtractor->getIterator());
    }
}
