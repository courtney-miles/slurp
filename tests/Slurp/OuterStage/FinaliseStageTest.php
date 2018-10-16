<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\OuterStage;

use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\OuterStage\FinaliseStage;
use MilesAsylum\Slurp\OuterStage\OuterStageInterface;
use MilesAsylum\Slurp\OuterStage\OuterStageObserverInterface;
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

    public function testObserverNotification()
    {
        $notifiedStates = [];

        $watchStates = function ($state) use (&$notifiedStates) {
            $notifiedStates[] = $state;
        };

        $observer = $this->createObserver($watchStates);

        $this->stage->attachObserver($observer);

        ($this->stage)($this->mockSlurp);

        $this->assertSame(
            [
                FinaliseStage::STATE_BEGIN,
                FinaliseStage::STATE_FINALISED,
                FinaliseStage::STATE_END,
            ],
            $notifiedStates
        );
    }

    protected function createObserver(callable $watchStates)
    {
        return new class($watchStates) implements OuterStageObserverInterface {
            /**
             * @var callable
             */
            private $watch;

            public function __construct(callable $watch)
            {
                $this->watch = $watch;
            }

            public function update(OuterStageInterface $stage): void
            {
                ($this->watch)($stage->getState());
            }
        };
    }
}
