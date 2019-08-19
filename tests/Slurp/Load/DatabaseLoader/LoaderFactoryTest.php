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

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PDO;
use PHPUnit\Framework\TestCase;

class LoaderFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var LoaderFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(PDO::class);
        $this->factory = new LoaderFactory($this->mockPdo);
    }

    public function testCreateStagedTable(): void
    {
        $this->assertInstanceOf(
            StagedLoad::class,
            $this->factory->createStagedLoad(
                'foo',
                ['col'],
                'bar'
            )
        );
    }

    public function testCreateBatchInsStmt(): void
    {
        $this->assertInstanceOf(
            BatchInsertManager::class,
            $this->factory->createBatchInsertManager(
                'foo',
                ['col'],
                'bar'
            )
        );
    }
}
