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

    public function testCannotTrickAFalseUniqueMatchWhenValuesContainNormalisationSeparator(): void
    {
        $rows = [
            ['pk_1' => 'foo', 'pk_2' => 'bar:'],
            ['pk_1' => 'foo:bar', 'pk_2' => ''],
        ];
        $sut = new EnforcePrimaryKeyIterator(
            new \ArrayIterator($rows),
            ['pk_1', 'pk_2']
        );
        $sut->rewind();

        try {
            foreach ($sut as $record) {
                // Do nothing
            }
        } catch (\Throwable $e) {
            self::fail('Enforcement of primary was tricked into a false match.');
        }

        // Perform an assertion to avoid warning of risky test, and ensure
        // the test increments the assertion count.
        self::assertTrue(true);
    }

    /**
     * The fundamental issue is that current() may be called twice without
     * incrementing the internal pointer. The side-effectful PK check in
     * current() must not throw a false duplicate on the second call.
     */
    public function testDoesNotFalsePositiveWhenCurrentCalledTwiceWithoutIncrementingPointer(): void
    {
        $rows = [
            ['pk_1' => 123, 'pk_2' => 'abc', 'foo' => 'bar'],
            ['pk_1' => 234, 'pk_2' => 'def', 'foo' => 'qux'],
        ];
        $sut = new EnforcePrimaryKeyIterator(
            new \ArrayIterator($rows),
            ['pk_1', 'pk_2']
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
            ['pk_1' => 123, 'pk_2' => 'abc', 'foo' => 'bar'],
            ['pk_1' => 234, 'pk_2' => 'def', 'foo' => 'qux'],
        ];
        $sut = new EnforcePrimaryKeyIterator(
            new \ArrayIterator($rows),
            ['pk_1', 'pk_2']
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
