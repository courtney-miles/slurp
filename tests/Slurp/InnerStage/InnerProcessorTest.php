<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 10:25 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\InnerStage;

use MilesAsylum\Slurp\InnerStage\InnerProcessor;
use MilesAsylum\Slurp\InnerStage\StageInterface;
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

    public function testNoInterruption()
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

    public function testInterruptOnFiltered()
    {
        $payload = new SlurpPayload();

        $mockStageOne = $this->createMockStage();
        $mockStageOne->shouldReceive('__invoke')
            ->withArgs(function (SlurpPayload $payload) {
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
