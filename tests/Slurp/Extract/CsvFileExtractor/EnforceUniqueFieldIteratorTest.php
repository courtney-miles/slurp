<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

use MilesAsylum\Slurp\Extract\CsvFileExtractor\EnforceUniqueFieldIterator;
use MilesAsylum\Slurp\Extract\Exception\DuplicateFieldValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Extract\CsvFileExtractor\EnforceUniqueFieldIterator
 */
class EnforceUniqueFieldIteratorTest extends TestCase
{
    public function testValueInEqualsValueOut(): void
    {
        $row = ['foo' => 123, 'bar' => 'abc'];
        $sut = new EnforceUniqueFieldIterator(
            new \ArrayIterator([$row]),
            []
        );
        $sut->rewind();

        self::assertSame($row, $sut->current());
    }

    public function testExceptionOnDuplicateFieldValue(): void
    {
        $this->expectException(DuplicateFieldValueException::class);
        $this->expectExceptionMessage('Duplicate value \'123\' found for field foo in record number 2.');
        $rows = [
            ['foo' => 123, 'bar' => 'abc'],
            ['foo' => 234, 'bar' => 'bce'],
            ['foo' => 123, 'bar' => 'def'],
        ];
        $sut = new EnforceUniqueFieldIterator(
            new \ArrayIterator($rows),
            ['foo']
        );
        $sut->rewind();

        foreach ($sut as $record) {
            // Do nothing
        }
    }

    /**
     * When CsvMultiFileExtractor wraps multiple iterators in an
     * AppendIterator, PHP's AppendIterator invokes current() twice on the
     * first record of each appended iterator. The side-effectful uniqueness
     * check in current() must not throw a false duplicate on the second call.
     */
    public function testDoesNotFalsePositiveWhenCurrentCalledTwiceForSameKeyViaAppendIterator(): void
    {
        $rows = [
            ['foo' => 123, 'bar' => 'abc'],
            ['foo' => 234, 'bar' => 'def'],
        ];
        $sut = new EnforceUniqueFieldIterator(
            new \ArrayIterator($rows),
            ['foo']
        );

        $appendIterator = new \AppendIterator();
        $appendIterator->append($sut);

        $iterated = [];
        foreach ($appendIterator as $key => $record) {
            $iterated[] = $record;
        }

        self::assertCount(2, $iterated);
        self::assertSame($rows[0], $iterated[0]);
        self::assertSame($rows[1], $iterated[1]);
    }
}
