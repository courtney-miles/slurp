<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use MilesAsylum\Slurp\Event\ExtractionFinalisedEvent;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $this->mockLoader->shouldReceive('finalise')
            ->byDefault();
        $this->mockSlurp = \Mockery::mock(Slurp::class);
        $this->mockSlurp->shouldReceive('isAborted')
            ->andReturn(false)
            ->byDefault();

        $this->stage = new FinaliseStage($this->mockLoader);
    }

    public function testFinaliseLoadOnInvoke()
    {
        $this->mockLoader->shouldReceive('finalise')
            ->once();

        $this->assertSame($this->mockSlurp, ($this->stage)($this->mockSlurp));
    }

    public function testsDoNotFinaliseIfLoadAborted()
    {
        $this->mockLoader->shouldReceive('isAborted')
            ->andReturn(true);
        $this->mockLoader->shouldReceive('finalise')
            ->never();

        ($this->stage)($this->mockSlurp);
    }

    public function testsDoNotFinaliseIfSlurpAborted()
    {
        $this->mockSlurp->shouldReceive('isAborted')
            ->andReturn(true);
        $this->mockLoader->shouldReceive('finalise')
            ->never();

        ($this->stage)($this->mockSlurp);
    }

    public function testFinalisationEventDispatched()
    {
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(ExtractionFinalisedEvent::NAME, \Mockery::type(ExtractionFinalisedEvent::class))
            ->once();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }
}
