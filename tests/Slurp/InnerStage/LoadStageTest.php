<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 10:43 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\InnerStage;

use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\InnerStage\LoadStage;
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
        $this->mockLoader->shouldReceive('loadValues')->byDefault();
        $this->mockLoader->shouldReceive('hasBegun')
            ->andReturn(true)
            ->byDefault();

        $this->stage = new LoadStage($this->mockLoader);
    }

    public function testMarkLoaderToBegin()
    {
        $mockPayload = $this->createMockPayload([], false);

        $this->mockLoader->shouldReceive('hasBegun')
            ->andReturn(false);
        $this->mockLoader->shouldReceive('begin')
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }

    public function testLoadValuesWhenInvoked()
    {
        $values = ['foo'];

        $mockPayload = $this->createMockPayload($values, false);

        $this->mockLoader->shouldReceive('loadValues')
            ->with($values)
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }

    public function testAbortOnViolation()
    {
        $mockViolatedPayload = $this->createMockPayload([], true);

        $this->mockLoader->shouldReceive('abort')
            ->once();
        $this->mockLoader->shouldReceive('loadValues')
            ->never();
        $mockViolatedPayload->shouldReceive('setLoadAborted')
            ->with(true)
            ->once();

        $this->assertSame($mockViolatedPayload, ($this->stage)($mockViolatedPayload));
    }

    public function testDoNotReBeginWhenPreviouslyAborted()
    {
        $mockViolatedPayload = $this->createMockPayload([], true);
        $mockPayload = $this->createMockPayload([], false);

        $this->mockLoader->shouldReceive('abort');

        $this->mockLoader->shouldReceive('hasBegun')
            ->andReturn(false);
        $this->mockLoader->shouldReceive('begin')
            ->once();
        $mockViolatedPayload->shouldReceive('setLoadAborted');
        $mockPayload->shouldReceive('setLoadAborted');

        ($this->stage)($mockViolatedPayload);
        ($this->stage)($mockPayload);
    }

    /**
     * @depends testAbortOnViolation
     */
    public function testDoNotLoadWhenPreviouslyAborted()
    {
        $mockViolatedPayload = $this->createMockPayload([], true);
        $mockPayload = $this->createMockPayload([], false);

        $this->mockLoader->shouldReceive('abort');
        $this->mockLoader->shouldReceive('loadValues')
            ->never();
        $mockViolatedPayload->shouldReceive('setLoadAborted');
        $mockPayload->shouldReceive('setLoadAborted')
            ->once();

        ($this->stage)($mockViolatedPayload);
        ($this->stage)($mockPayload);
    }

    /**
     * @param array $values
     * @param bool $hasViolations
     * @return SlurpPayload|MockInterface
     */
    protected function createMockPayload(array $values, bool $hasViolations)
    {
        /** @var SlurpPayload|MockInterface $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('getRecord')
            ->andReturn($values);
        $mockPayload->shouldReceive('hasViolations')
            ->andReturn($hasViolations);

        return $mockPayload;
    }
}
