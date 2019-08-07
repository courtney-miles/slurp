<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:15 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use League\Csv\Reader;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor
 */
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

    public function testSetDelimiter(): void
    {
        $delimiter = '!';
        $this->mockReader->shouldReceive('setDelimiter')
            ->with($delimiter)
            ->once();

        $this->csvExtractor->setDelimiter($delimiter);
    }

    public function testSetEnclosure(): void
    {
        $enclosure = '!';
        $this->mockReader->shouldReceive('setEnclosure')
            ->with($enclosure)
            ->once();

        $this->csvExtractor->setEnclosure($enclosure);
    }

    public function testSetEscape(): void
    {
        $escape = '!';
        $this->mockReader->shouldReceive('setEscape')
            ->with($escape)
            ->once();

        $this->csvExtractor->setEscape($escape);
    }

    public function testNoHeaders(): void
    {
        $csvRows = [[123, 234]];
        $this->setUpMockReader($this->mockReader, $csvRows);

        foreach ($this->csvExtractor as $rowId => $row) {
            $this->assertArrayHasKey($rowId, $csvRows);
            $this->assertSame($csvRows[$rowId], $row);
        }
    }

    public function testUseHeadersFromFile(): void
    {
        $csvRows = [
            ['col_1', 'col_2'],
            [123, 234]
        ];
        $this->setUpMockReader($this->mockReader, $csvRows);
        $this->csvExtractor->loadHeadersFromFile();

        $records = iterator_to_array($this->csvExtractor);

        $this->assertCount(1, $records);
        $this->assertSame(array_combine($csvRows[0], $csvRows[1]), reset($records));
    }

    public function testSetHeaders(): void
    {
        $headers = ['col_1', 'col_2'];
        $csvRows = [[123, 234]];
        $this->setUpMockReader($this->mockReader, $csvRows);
        $this->csvExtractor->setHeaders($headers);

        foreach ($this->csvExtractor as $rowId => $row) {
            $this->assertArrayHasKey($rowId, $csvRows);
            $this->assertSame(array_combine($headers, $csvRows[$rowId]), $row);
        }
    }

    public function testExceptionOnInconsistentNumberOfRowValuesWithoutHeaders(): void
    {
        $this->expectException(ValueCountMismatchException::class);

        $csvRows = [[123, 234],[345,456,567]];
        $this->setUpMockReader($this->mockReader, $csvRows);

        foreach ($this->csvExtractor as $rowId => $row) {
            // Do nothing.
        }
    }

    public function testExceptionOnInconsistentNumberOfRowValuesWithHeaders(): void
    {
        $this->expectException(ValueCountMismatchException::class);

        $csvRows = [[123, 234, 345]];
        $this->setUpMockReader($this->mockReader, $csvRows);
        $this->csvExtractor->setHeaders(['col_1', 'col_2']);

        foreach ($this->csvExtractor as $rowId => $row) {
            // Do nothing.
        }
    }

    public function setUpMockReader(MockInterface $mockReader, array $rows): void
    {
        $mockReader->shouldReceive('fetchOne')
            ->withNoArgs()
            ->andReturn($rows[0]);

        $mockReader->shouldReceive('getRecords')
            ->withNoArgs()
            ->andReturn(new \ArrayIterator($rows));
    }
}
