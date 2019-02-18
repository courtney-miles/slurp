<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:21 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Event\ExtractionEndedEvent;
use MilesAsylum\Slurp\Event\ExtractionStartedEvent;
use MilesAsylum\Slurp\Event\RecordProcessedEvent;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\OuterPipeline\OuterStageInterface;
use MilesAsylum\Slurp\OuterPipeline\OuterStageObserverInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtractionStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ExtractionStage
     */
    protected $stage;

    /**
     * @var Pipeline|MockInterface
     */
    protected $mockPipeline;

    /**
     * @var Slurp|MockInterface
     */
    protected $mockSlurp;

    public function setUp()
    {
        parent::setUp();

        $this->mockPipeline = \Mockery::mock(Pipeline::class);
        $this->mockSlurp = \Mockery::mock(Slurp::class);

        $this->stage = new ExtractionStage($this->mockPipeline);
    }

    public function testIterateExtractionOnInvoke()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('__invoke')
            ->withArgs(
                function ($payload) use ($rows) {
                    if (!$payload instanceof SlurpPayload) {
                        return false;
                    }

                    if (!isset($rows[$payload->getRecordId()])) {
                        return false;
                    }

                    if ($rows[$payload->getRecordId()] !== $payload->getRecord()) {
                        return false;
                    }

                    return true;
                }
            )->times(count($rows));

        $this->assertSame($this->mockSlurp, ($this->stage)($this->mockSlurp));
    }

    public function testAbortOnInterrupt()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $interrupt = function (Slurp $slurp, SlurpPayload $payload) {
            return true;
        };
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockSlurp->shouldReceive('abort')->once();

        $this->mockPipeline->shouldReceive('__invoke')
            ->with(\Mockery::type(SlurpPayload::class))
            ->once();

        $stage = new ExtractionStage($this->mockPipeline, $interrupt);
        ($stage)($this->mockSlurp);
    }

    public function testDispatchExtractionStartedAndEndedEvents()
    {
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, []);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);

        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(ExtractionStartedEvent::NAME, \Mockery::type(ExtractionStartedEvent::class))
            ->once();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(ExtractionEndedEvent::NAME, \Mockery::type(ExtractionEndedEvent::class))
            ->once();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }

    public function testDispatchRecordProcessedEvent()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);

        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('__invoke');

        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->byDefault();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(RecordProcessedEvent::NAME, \Mockery::type(RecordProcessedEvent::class))
            ->twice();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }


    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues)
    {
        $mockExtractor->shouldReceive('getIterator')
            ->andReturn(new \ArrayObject($rowValues));
    }
}
