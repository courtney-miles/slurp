<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\CsvFileExtractor\EnforcePrimaryKeyIterator;
use MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Extract\CsvFileExtractor\EnforcePrimaryKeyIterator
 */
class EnforcePrimaryKeyIteratorTest extends TestCase
{
    public function testValueInEqualsValueOut(): void
    {
        $row = ['foo' => 123, 'bar' => 'abc'];
        $sut = new EnforcePrimaryKeyIterator(
            new \ArrayIterator([$row]),
            []
        );
        $sut->rewind();

        self::assertSame($row, $sut->current());
    }

    public function testExceptionOnDuplicatePrimaryKeyValue(): void
    {
        $this->expectException(DuplicatePrimaryKeyValueException::class);
        $this->expectExceptionMessage('Duplicate value \'123:abc\' found for primary key pk_1:pk_2 in record number 2.');

        $rows = [
            ['pk_1' => 123, 'pk_2' => 'abc', 'foo' => 'bar'],
            ['pk_1' => 123, 'pk_2' => 'bce', 'foo' => 'qux'],
            ['pk_1' => 123, 'pk_2' => 'abc', 'foo' => 'baz'],
        ];
        $sut = new EnforcePrimaryKeyIterator(
            new \ArrayIterator($rows),
            ['pk_1', 'pk_2']
        );
        $sut->rewind();

        foreach ($sut as $record) {
            // Do nothing
        }
    }
}
