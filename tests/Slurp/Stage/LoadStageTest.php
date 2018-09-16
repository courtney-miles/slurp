<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 10:43 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\LoadStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class LoadStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var LoadStage
     */
    protected $stage;

    /**
     * @var LoaderInterface|MockInterface
     */
    protected $mockLoader;

    public function setUp()
    {
        parent::setUp();

        $this->mockLoader = \Mockery::mock(LoaderInterface::class);

        $this->stage = new LoadStage($this->mockLoader);
    }

    public function testLoadValuesWhenInvoked()
    {
        $values = ['foo'];
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('getValues')
            ->andReturn($values);
        $mockPayload->shouldReceive('hasViolations')
            ->andReturn(false);


        $this->mockLoader->shouldReceive('loadValues')
            ->with($values)
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }
}
