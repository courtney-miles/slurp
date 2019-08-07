<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 10:25 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\InnerPipeline;

use MilesAsylum\Slurp\InnerPipeline\InnerProcessor;
use MilesAsylum\Slurp\InnerPipeline\StageInterface;
use MilesAsylum\Slurp\SlurpPayload;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class InnerProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var InnerProcessor
     */
    protected $processor;

    public function setUp()
    {
        parent::setUp();

        $this->processor = new InnerProcessor();
    }

    public function testNoInterruption(): void
    {
        $payload = new SlurpPayload();

        $mockStageOne = $this->createMockStage();
        $mockStageOne->shouldReceive('__invoke')
            ->andReturn($payload)
            ->once();

        $mockStageTwo = $this->createMockStage();
        $mockStageTwo->shouldReceive('__invoke')
            ->andReturn($payload)
            ->once();

        $this->assertSame($payload, $this->processor->process($payload, $mockStageOne, $mockStageTwo));
    }

    public function testInterruptOnFiltered(): void
    {
        $payload = new SlurpPayload();

        $mockStageOne = $this->createMockStage();
        $mockStageOne->shouldReceive('__invoke')
            ->withArgs(static function (SlurpPayload $payload) {
                $payload->setFiltered(true);
                return true;
            })->andReturn($payload);

        $mockStageTwo = $this->createMockStage();
        $mockStageTwo->shouldReceive('__invoke')
            ->never();

        $this->assertSame($payload, $this->processor->process($payload, $mockStageOne, $mockStageTwo));
    }

    /**
     * @return MockInterface|StageInterface
     */
    protected function createMockStage(): MockInterface
    {
        $mockStage = \Mockery::mock(StageInterface::class);

        return $mockStage;
    }
}
