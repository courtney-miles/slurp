<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:36 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Slurp;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Slurp
 */
class SlurpTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testAbort(): void
    {
        $slurp = new Slurp(Mockery::mock(Pipeline::class));

        $this->assertFalse($slurp->isAborted());
        $slurp->abort();
        $this->assertTrue($slurp->isAborted());
    }

    public function testProcess(): void
    {
        $mockExtractor = Mockery::mock(ExtractorInterface::class);
        $mockPipeLine = Mockery::spy(Pipeline::class);

        $slurp = new Slurp($mockPipeLine);
        $slurp->process($mockExtractor);

        $mockPipeLine->shouldHaveReceived('__invoke')
            ->with($slurp)
            ->once();
    }

    public function testExtractorIsNullBeforeProcessing(): void
    {
        $mockPipeLine = Mockery::mock(Pipeline::class);
        $slurp = new Slurp($mockPipeLine);

        $this->assertNull($slurp->getExtractor());
    }

    public function testGetExtractorWhilstProcessing(): void
    {
        $mockExtractor = Mockery::mock(ExtractorInterface::class);
        $mockPipeLine = Mockery::mock(Pipeline::class);
        $mockPipeLine->shouldReceive('__invoke')
            ->withArgs(function (Slurp $slurp) use ($mockExtractor) {
                $this->assertSame($mockExtractor, $slurp->getExtractor());
                return true;
            });

        $slurp = new Slurp($mockPipeLine);
        $slurp->process($mockExtractor);
    }

    public function testExtractorIsNullAfterProcessing(): void
    {
        $mockExtractor = Mockery::mock(ExtractorInterface::class);
        $mockPipeLine = Mockery::mock(Pipeline::class);
        $mockPipeLine->shouldReceive('__invoke');

        $slurp = new Slurp($mockPipeLine);
        $slurp->process($mockExtractor);
        $this->assertNull($slurp->getExtractor());
    }
}
