<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:21 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\OuterPipeline\OuterStageInterface;
use MilesAsylum\Slurp\OuterPipeline\OuterStageObserverInterface;
use MilesAsylum\Slurp\Validate\RecordViolation;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

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

    public function testObserverNotification()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('__invoke');

        $notifiedStates = [];

        $watchStates = function ($state) use (&$notifiedStates) {
            $notifiedStates[] = $state;
        };

        $observer = $this->createObserver($watchStates);

        $this->stage->attachObserver($observer);

        ($this->stage)($this->mockSlurp);

        $this->assertSame(
            [
                ExtractionStage::STATE_BEGIN,
                ExtractionStage::STATE_RECORD_PROCESSED,
                ExtractionStage::STATE_RECORD_PROCESSED,
                ExtractionStage::STATE_END,
            ],
            $notifiedStates
        );
    }


    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues)
    {
        $mockExtractor->shouldReceive('getIterator')
            ->andReturn(new \ArrayObject($rowValues));
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
