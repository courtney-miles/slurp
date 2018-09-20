<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 12:21 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class InvokeExtractionPipelineTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var InvokeExtractionPipeline
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

        $this->stage = new InvokeExtractionPipeline($this->mockPipeline);
    }

    public function testIterateExtractionOnInvoke()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->stubExtractorContent($mockExtractor, $rows);
        $this->mockSlurp->shouldReceive('getExtractor')->andReturn($mockExtractor);
        $this->mockPipeline->shouldReceive('process')
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

    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues)
    {
        $mockExtractor->shouldReceive('getIterator')
            ->andReturn(new \ArrayObject($rowValues));
    }
}
