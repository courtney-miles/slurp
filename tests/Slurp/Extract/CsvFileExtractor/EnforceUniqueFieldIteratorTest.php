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
     * The fundamental issue is that current() may be called twice without
     * incrementing the internal pointer. The side-effectful uniqueness check
     * in current() must not throw a false duplicate on the second call.
     */
    public function testDoesNotFalsePositiveWhenCurrentCalledTwiceWithoutIncrementingPointer(): void
    {
        $rows = [
            ['foo' => 123, 'bar' => 'abc'],
            ['foo' => 234, 'bar' => 'def'],
        ];
        $sut = new EnforceUniqueFieldIterator(
            new \ArrayIterator($rows),
            ['foo']
        );
        $sut->rewind();

        $firstResult = $sut->current();
        $secondResult = $sut->current();

        self::assertSame($firstResult, $secondResult);
        self::assertSame($rows[0], $secondResult);
    }

    /**
     * rewind() is not expected to be called during normal extraction, but it
     * is a public method so it should be valid to use it without causing the
     * same issue as two calls to current() do.
     */
    public function testCanReiterateAfterRewind(): void
    {
        $rows = [
            ['foo' => 123, 'bar' => 'abc'],
            ['foo' => 234, 'bar' => 'def'],
        ];
        $sut = new EnforceUniqueFieldIterator(
            new \ArrayIterator($rows),
            ['foo']
        );

        $firstPass = [];
        foreach ($sut as $record) {
            $firstPass[] = $record;
        }

        $sut->rewind();

        $secondPass = [];
        foreach ($sut as $record) {
            $secondPass[] = $record;
        }

        self::assertCount(2, $firstPass);
        self::assertCount(2, $secondPass);
        self::assertSame($rows[0], $secondPass[0]);
        self::assertSame($rows[1], $secondPass[1]);
    }
}
