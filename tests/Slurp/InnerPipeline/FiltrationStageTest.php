<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\InnerPipeline;

use MilesAsylum\Slurp\Filter\FilterInterface;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\Event\RecordFilteredEvent;
use MilesAsylum\Slurp\SlurpPayload;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FiltrationStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FiltrationStage
     */
    protected $stage;

    /**
     * @var FilterInterface|MockInterface
     */
    protected $mockFilter;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilter = \Mockery::mock(FilterInterface::class);
        $this->mockFilter->shouldReceive('filterRecord')
            ->andReturn(false)
            ->byDefault();
        $this->stage = new FiltrationStage($this->mockFilter);
    }

    public function testFilterOnInvoke()
    {
        $record = ['foo' => 123];
        $payload = new SlurpPayload();
        $payload->setRecord($record);

        $this->mockFilter->shouldReceive('filterRecord')
            ->with($record)
            ->andReturn(true);

        $this->stage->__invoke($payload);

        $this->assertTrue($payload->isFiltered());
    }

    public function testDispatchEventOnFiltered()
    {
        $payload = new SlurpPayload();
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(RecordFilteredEvent::NAME, \Mockery::type(RecordFilteredEvent::class))
            ->once();

        $this->mockFilter->shouldReceive('filterRecord')
            ->andReturn(true);

        $this->stage->setEventDispatcher($mockDispatcher);
        ($this->stage)($payload);
    }

    public function testDoNotDispatchEventOnNotFiltered()
    {
        $payload = new SlurpPayload();
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->never();

        $this->mockFilter->shouldReceive('filterRecord')
            ->andReturn(false);

        $this->stage->setEventDispatcher($mockDispatcher);
        ($this->stage)($payload);
    }
}
