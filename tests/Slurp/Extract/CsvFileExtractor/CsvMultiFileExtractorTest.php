<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvFileExtractor;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\CsvMultiFileExtractor;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class CsvMultiFileExtractorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetDelimiter(): void
    {
        $delimiter = '!';
        $mockCsvFileExtractor1 = \Mockery::mock(CsvFileExtractor::class);
        $mockCsvFileExtractor2 = \Mockery::mock(CsvFileExtractor::class);

        $mockCsvFileExtractor1->shouldReceive('setDelimiter')
            ->with($delimiter)
            ->once();
        $mockCsvFileExtractor2->shouldReceive('setDelimiter')
            ->with($delimiter)
            ->once();

        $x = new CsvMultiFileExtractor([$mockCsvFileExtractor1, $mockCsvFileExtractor2]);
        $x->setDelimiter($delimiter);
    }

    public function testSetEnclosure(): void
    {
        $enclosure = '!';
        $mockCsvFileExtractor1 = \Mockery::mock(CsvFileExtractor::class);
        $mockCsvFileExtractor2 = \Mockery::mock(CsvFileExtractor::class);

        $mockCsvFileExtractor1->shouldReceive('setEnclosure')
            ->with($enclosure)
            ->once();
        $mockCsvFileExtractor2->shouldReceive('setEnclosure')
            ->with($enclosure)
            ->once();

        $x = new CsvMultiFileExtractor([$mockCsvFileExtractor1, $mockCsvFileExtractor2]);
        $x->setEnclosure($enclosure);
    }

    public function testSetEscape(): void
    {
        $escape = '!';
        $mockCsvFileExtractor1 = \Mockery::mock(CsvFileExtractor::class);
        $mockCsvFileExtractor2 = \Mockery::mock(CsvFileExtractor::class);

        $mockCsvFileExtractor1->shouldReceive('setEscape')
            ->with($escape)
            ->once();
        $mockCsvFileExtractor2->shouldReceive('setEscape')
            ->with($escape)
            ->once();

        $x = new CsvMultiFileExtractor([$mockCsvFileExtractor1, $mockCsvFileExtractor2]);
        $x->setEscape($escape);
    }

    public function testLoadHeadersFromFile(): void
    {
        $mockCsvFileExtractor1 = \Mockery::mock(CsvFileExtractor::class);
        $mockCsvFileExtractor2 = \Mockery::mock(CsvFileExtractor::class);

        $mockCsvFileExtractor1->shouldReceive('loadHeadersFromFile')
            ->once();
        $mockCsvFileExtractor2->shouldReceive('loadHeadersFromFile')
            ->once();

        $x = new CsvMultiFileExtractor([$mockCsvFileExtractor1, $mockCsvFileExtractor2]);
        $x->loadHeadersFromFile();
    }

    public function testSetHeaders(): void
    {
        $headers = ['foo', 'bar'];
        $mockCsvFileExtractor1 = \Mockery::mock(CsvFileExtractor::class);
        $mockCsvFileExtractor2 = \Mockery::mock(CsvFileExtractor::class);

        $mockCsvFileExtractor1->shouldReceive('setHeaders')
            ->with($headers)
            ->once();
        $mockCsvFileExtractor2->shouldReceive('setHeaders')
            ->with($headers)
            ->once();

        $x = new CsvMultiFileExtractor([$mockCsvFileExtractor1, $mockCsvFileExtractor2]);
        $x->setHeaders($headers);
    }
}
