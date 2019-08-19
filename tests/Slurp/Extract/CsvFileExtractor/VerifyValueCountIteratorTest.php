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

use ArrayIterator;
use MilesAsylum\Slurp\Extract\CsvFileExtractor\VerifyValueCountIterator;
use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use PHPUnit\Framework\TestCase;

class VerifyValueCountIteratorTest extends TestCase
{
    /**
     * @dataProvider getValueCountMismatchTestData
     * @param array $values
     * @param int $expectedCount
     * @throws ValueCountMismatchException
     */
    public function testValueCountMismatch(array $values, int $expectedCount): void
    {
        $this->expectException(ValueCountMismatchException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Record 0 contained %s values where we expected %d.',
                count($values),
                $expectedCount
            )
        );

        $iterator = new VerifyValueCountIterator(
            new ArrayIterator([$values]),
            $expectedCount
        );

        $iterator->rewind();
        $iterator->current();
    }

    public function getValueCountMismatchTestData(): array
    {
        return [
            [[123,234], 1],
            [[123,234], 3],
        ];
    }
}
