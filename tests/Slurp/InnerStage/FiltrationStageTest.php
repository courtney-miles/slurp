<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\InnerStage;

use MilesAsylum\Slurp\Filter\FilterInterface;
use MilesAsylum\Slurp\InnerStage\FiltrationStage;
use MilesAsylum\Slurp\SlurpPayload;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

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
}
