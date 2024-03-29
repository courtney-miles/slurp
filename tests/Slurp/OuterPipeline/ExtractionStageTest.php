<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Event\ExtractionEndedEvent;
use MilesAsylum\Slurp\Event\ExtractionFailedEvent;
use MilesAsylum\Slurp\Event\ExtractionStartedEvent;
use MilesAsylum\Slurp\Event\RecordProcessedEvent;
use MilesAsylum\Slurp\Extract\Exception\ExtractionException;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->mockPipeline = \Mockery::mock(Pipeline::class);
        $this->mockSlurp = \Mockery::mock(Slurp::class);

        $this->stage = new ExtractionStage($this->mockPipeline);
    }

    public function testIterateExtractionOnInvoke(): void
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('__invoke')
            ->withArgs(
                static function ($payload) use ($rows) {
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

    public function testAbortOnInterrupt(): void
    {
        $rows = [['foo', 123], ['bar', 234]];
        $interrupt = static function (Slurp $slurp, SlurpPayload $payload) {
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

    public function testDispatchExtractionStartedAndEndedEvents(): void
    {
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, []);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);

        $mockDispatcher = $this->createMockDispatcher();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(ExtractionStartedEvent::class), ExtractionStartedEvent::NAME)
            ->once();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(ExtractionEndedEvent::class), ExtractionEndedEvent::NAME)
            ->once();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }

    public function testDispatchRecordProcessedEvent(): void
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);

        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('__invoke');

        $mockDispatcher = $this->createMockDispatcher();
        $mockDispatcher->shouldReceive('dispatch')->byDefault();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(RecordProcessedEvent::class), RecordProcessedEvent::NAME)
            ->twice();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);
    }

    public function testAbortOnExtractionException(): void
    {
        $rows = [['foo', 123], ['bar', 234]];
        $exceptionAtCount = 1;
        $exceptionMessage = 'Fubar';
        /** @var ExtractionFailedEvent|null $spiedEvent This is used to capture the event so we can perform assertions against it. */
        $spiedEvent = null;
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContentWithException($mockExtractor, $rows, $exceptionAtCount, $exceptionMessage);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockSlurp->shouldReceive('abort')->once();
        $this->mockPipeline->shouldReceive('__invoke');

        $mockDispatcher = $this->createMockDispatcher();
        $mockDispatcher->shouldReceive('dispatch')
            ->andReturnUsing(static function ($event, $eventName) {
                return $event;
            })->byDefault();
        $mockDispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(ExtractionFailedEvent::class), ExtractionFailedEvent::NAME)
            ->andReturnUsing(static function ($event, $eventName) use (&$spiedEvent) {
                $spiedEvent = $event;

                return $event;
            })
            ->once();
        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($this->mockSlurp);

        $this->assertInstanceOf(ExtractionFailedEvent::class, $spiedEvent);
        $this->assertSame($exceptionAtCount, $spiedEvent->getRecordId());
        $this->assertSame($exceptionMessage, $spiedEvent->getReason());
    }

    /**
     * @return MockInterface|EventDispatcherInterface
     */
    protected function createMockDispatcher(): MockInterface
    {
        return \Mockery::mock(EventDispatcherInterface::class);
    }

    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues): void
    {
        $mockExtractor->shouldReceive('getIterator')
            ->andReturn(new \ArrayObject($rowValues));
    }

    protected function stubExtractorContentWithException(
        MockInterface $mockExtractor,
        array $rowValues,
        int $exceptionAtCount,
        string $exceptionMessage
    ): void {
        $iteratorWithException = new class($rowValues, $exceptionAtCount, $exceptionMessage) extends \IteratorIterator {
            protected $exceptionAtCount;
            /**
             * @var string
             */
            private $exceptionMessage;

            public function __construct(array $rows, int $exceptionAtCount, string $exceptionMessage)
            {
                $this->exceptionAtCount = $exceptionAtCount;
                $this->exceptionMessage = $exceptionMessage;
                parent::__construct(new \ArrayObject($rows));
            }

            #[\ReturnTypeWillChange]
            public function current()
            {
                if ($this->key() !== $this->exceptionAtCount) {
                    return parent::current();
                }

                throw new ExtractionException($this->exceptionMessage);
            }
        };

        $mockExtractor->shouldReceive('getIterator')
            ->andReturn($iteratorWithException);
    }
}
