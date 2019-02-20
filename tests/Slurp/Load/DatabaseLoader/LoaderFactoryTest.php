<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 6:33 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\DatabaseLoader\BatchInsertManager;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\StagedLoad;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class LoaderFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    /**
     * @var LoaderFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);
        $this->factory = new LoaderFactory($this->mockPdo);
    }

    public function testCreateStagedTable()
    {
        $this->assertInstanceOf(
            StagedLoad::class,
            $this->factory->createStagedLoad(
                'foo',
                ['col']
            )
        );
    }

    public function testCreateBatchInsStmt()
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
