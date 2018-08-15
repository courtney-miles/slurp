<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:15 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Extract;

use MilesAsylum\Slurp\Extract\CsvFileExtractor;
use MilesAsylum\Slurp\Extract\Exception\MalformedCsvException;
use PHPUnit\Framework\TestCase;

class CsvFileExtractorTest extends TestCase
{
    public function testGetColumnNamesWithHeaders()
    {
        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/with_headers.csv', true);

        $this->assertSame(
            ['user','date','class','value'],
            $csv->getColumnNames()
        );
    }

    public function testGetColumnNamesWithoutHeaders()
    {
        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/without_headers.csv', false);

        $this->assertSame(
            [],
            $csv->getColumnNames()
        );
    }

    public function testIterateFileWithHeaders()
    {
        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/with_headers.csv', true);

        $this->assertSame(
            [
                1 => [
                    'user' => 'user123',
                    'date' => '2018-01-01',
                    'class' => 'foo',
                    'value' => '123.45'
                ],
                2 => [
                    'user' => 'user456',
                    'date' => '2018-02-01',
                    'class' => 'bar',
                    'value' => '678.90'
                ]
            ],
            $this->iterateCsv($csv)
        );
    }

    /**
     * @depends testIterateFileWithHeaders
     */
    public function testRewindIterator()
    {
        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/with_headers.csv', true);

        $this->assertSame($this->iterateCsv($csv), $this->iterateCsv($csv));
    }

    public function testIterateFileWithoutHeaders()
    {
        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/without_headers.csv', false);

        $this->assertSame(
            [
                0 => ['user123', '2018-01-01', 'foo', '123.45'],
                1 => ['user456', '2018-02-01', 'bar', '678.90']
            ],
            $this->iterateCsv($csv)
        );
    }

    public function testIterateFileWithMismatchColumnCount()
    {
        $this->expectException(MalformedCsvException::class);
        $this->expectExceptionMessage(
            'Line 1 in ' . __DIR__ . '/_fixture/mismatch_column_count.csv has 5 values where we expected 4.'
        );

        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/mismatch_column_count.csv', true);

        $this->iterateCsv($csv);
    }

    public function testIterateFileWithVaryingColumnCount()
    {
        $this->expectException(MalformedCsvException::class);
        $this->expectExceptionMessage(
            'Line 1 in ' . __DIR__ . '/_fixture/varying_column_count.csv has 3 values where previous rows had 4.'
        );

        $csv = new CsvFileExtractor(__DIR__ . '/_fixture/varying_column_count.csv', false);

        $this->iterateCsv($csv);
    }

    public function testConstructFileWithDuplicateColumnNames()
    {
        $this->expectException(MalformedCsvException::class);
        $this->expectExceptionMessage(
            'The loaded file ' . __DIR__ . '/_fixture/duplicate_headers.csv contains duplicate column names.'
        );

        new CsvFileExtractor(__DIR__ . '/_fixture/duplicate_headers.csv', true);
    }

    protected function iterateCsv(CsvFileExtractor $csv)
    {
        $results = [];

        foreach ($csv as $linoNo => $arr) {
            $results[$linoNo] = $arr;
        }

        return $results;
    }
}
