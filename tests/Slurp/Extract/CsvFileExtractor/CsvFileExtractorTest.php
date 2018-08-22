<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use League\Csv\Reader;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CsvFileExtractorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var CsvFileExtractor
     */
    protected $csvExtractor;

    /**
     * @var Reader|MockInterface
     */
    protected $mockReader;

    public function setUp()
    {
        parent::setUp();

        $this->mockReader = \Mockery::mock(Reader::class);
        $this->csvExtractor = new CsvFileExtractor($this->mockReader);
    }

    public function testLoadHeaders()
    {
        $this->mockReader->shouldReceive('setHeaderOffset')
            ->with(0)
            ->once();

        $this->csvExtractor->loadHeadersFromFile();
    }

    public function testUseSuppliedHeaders()
    {
        $headers = ['col 1', 'col 2'];
        $mockIterator = \Mockery::mock(\Iterator::class);
        $this->mockReader->shouldReceive('getRecords')
            ->with($headers)
            ->andReturn($mockIterator);

        $this->csvExtractor->setHeaders($headers);

        $this->assertSame($mockIterator, $this->csvExtractor->getIterator());
    }
}
