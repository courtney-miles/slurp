<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use MilesAsylum\Slurp\Event\ExtractionFinalisationBeginEvent;
use MilesAsylum\Slurp\Event\ExtractionFinalisationCompleteEvent;
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

        $this->mockLoader = $this->createMockLoader();
        $this->mockSlurp = $this->createMockSlurp();
        $this->stage = new FinaliseStage($this->mockLoader);
    }

    public function testFinaliseLoadOnInvoke()
    {
        $this->mockLoader->shouldReceive('finalise')
            ->once();

        $this->assertSame($this->mockSlurp, ($this->stage)($this->mockSlurp));
    }

    public function testsDoNotFinaliseIfNotBegun()
    {
        $this->mockLoader->shouldReceive('hasBegun')
            ->andReturn(false);
        $this->mockLoader->shouldReceive('finalise')
            ->never();

        ($this->stage)($this->mockSlurp);
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

    public function testFinalisationEventsDispatched()
    {
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(
                ExtractionFinalisationBeginEvent::NAME,
                \Mockery::type(ExtractionFinalisationBeginEvent::class)
            )->once();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(
                ExtractionFinalisationCompleteEvent::NAME,
                \Mockery::type(ExtractionFinalisationCompleteEvent::class)
            )->once();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }

    /**
     * @return MockInterface|LoaderInterface
     */
    protected function createMockLoader(): MockInterface
    {
        $mockLoader = \Mockery::mock(LoaderInterface::class);
        $mockLoader->shouldReceive('hasBegun')
            ->andReturn(true)
            ->byDefault();
        $mockLoader->shouldReceive('isAborted')
            ->andReturn(false)
            ->byDefault();
        $mockLoader->shouldReceive('finalise')
            ->byDefault();

        return $mockLoader;
    }

    /**
     * @return MockInterface|Slurp
     */
    protected function createMockSlurp(): MockInterface
    {
        $mockSlurp = \Mockery::mock(Slurp::class);
        $mockSlurp->shouldReceive('isAborted')
            ->andReturn(false)
            ->byDefault();

        return $mockSlurp;
    }
}
