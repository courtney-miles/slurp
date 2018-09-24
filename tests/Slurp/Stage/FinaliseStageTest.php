<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\Stage\FinaliseStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class FinaliseStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FinaliseStage
     */
    protected $stage;

    /**
     * @var LoaderInterface|MockInterface
     */
    protected $mockLoader;

    /**
     * @var Slurp|MockInterface
     */
    protected $mockSlurp;

    public function setUp()
    {
        parent::setUp();

        $this->mockLoader = \Mockery::mock(LoaderInterface::class);
        $this->mockLoader->shouldReceive('isAborted')
            ->andReturn(false)
            ->byDefault();
        $this->mockSlurp = \Mockery::mock(Slurp::class);

        $this->stage = new FinaliseStage($this->mockLoader);
    }

    public function testFinaliseLoadOnInvoke()
    {
        $this->mockLoader->shouldReceive('finalise')
            ->once();

        $this->assertSame($this->mockSlurp, ($this->stage)($this->mockSlurp));
    }

    public function testsDoNotFinaliseIfAborted()
    {
        $this->mockLoader->shouldReceive('isAborted')
            ->andReturn(true);
        $this->mockLoader->shouldReceive('finalise')
            ->never();
    }
}
