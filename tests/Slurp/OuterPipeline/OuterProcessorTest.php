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

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use MilesAsylum\Slurp\OuterPipeline\OuterProcessor;
use MilesAsylum\Slurp\OuterPipeline\OuterStageInterface;
use MilesAsylum\Slurp\Slurp;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class OuterProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var OuterProcessor
     */
    protected $processor;

    public function setUp(): void
    {
        parent::setUp();

        $this->processor = new OuterProcessor();
    }

    public function testNoInterruption(): void
    {
        $mockSlurp = Mockery::mock(Slurp::class);
        $mockSlurp->shouldReceive('isAborted')
            ->andReturn(false);

        $mockStageOne = $this->createMockStage();
        $mockStageOne->shouldReceive('__invoke')
            ->andReturn($mockSlurp)
            ->once();

        $mockStageTwo = $this->createMockStage();
        $mockStageTwo->shouldReceive('__invoke')
            ->andReturn($mockSlurp)
            ->once();

        $this->assertSame($mockSlurp, $this->processor->process($mockSlurp, $mockStageOne, $mockStageTwo));
    }

    public function testInterruptOnAbort(): void
    {
        $abort = false;
        $mockSlurp = Mockery::mock(Slurp::class);
        $mockSlurp->shouldReceive('isAborted')
            ->andReturnUsing(static function () use (&$abort) {
                return $abort;
            });

        $mockStageOne = $this->createMockStage();
        $mockStageOne->shouldReceive('__invoke')
            ->withArgs(static function (Slurp $slurp) use (&$abort) {
                $abort = true;

                return true;
            })->andReturn($mockSlurp);

        $mockStageTwo = $this->createMockStage();
        $mockStageTwo->shouldReceive('__invoke')
            ->never();

        $this->assertSame($mockSlurp, $this->processor->process($mockSlurp, $mockStageOne, $mockStageTwo));
    }

    /**
     * @return MockInterface|OuterStageInterface
     */
    protected function createMockStage(): MockInterface
    {
        $mockStage = Mockery::mock(OuterStageInterface::class);

        return $mockStage;
    }
}
