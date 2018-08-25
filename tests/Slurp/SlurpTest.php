<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:36 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Transform\TransformerBork;
use MilesAsylum\Slurp\Validate\Validator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Slurp
 */
class SlurpTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testProcess()
    {
        $rows = [['foo', 123], ['bar', 234]];
        $mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $mockPipeLine = \Mockery::mock(Pipeline::class);

        $this->stubExtractorContent($mockExtractor, $rows);

        $mockPipeLine->shouldReceive('process')
            ->withArgs(
                function ($payload) use ($rows) {
                    if (!$payload instanceof SlurpPayload) {
                        return false;
                    }

                    if (!isset($rows[$payload->getRowId()])) {
                        return false;
                    }

                    if ($rows[$payload->getRowId()] !== $payload->getValues()) {
                        return false;
                    }

                    return true;
                }
            )->times(count($rows));

        $slurp = new Slurp($mockPipeLine);
        $slurp->process($mockExtractor);
    }

    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues)
    {
        $mockExtractor->shouldReceive('getIterator')
            ->andReturn(new \ArrayObject($rowValues));
    }
}
