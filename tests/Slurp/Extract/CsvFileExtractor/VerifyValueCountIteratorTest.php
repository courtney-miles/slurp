<?php
/**
 * Author: Courtney Miles
 * Date: 27/08/18
 * Time: 6:53 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\CsvFileExtractor;

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
    public function testValueCountMismatch(array $values, int $expectedCount)
    {
        $this->expectException(ValueCountMismatchException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Record 0 contained %s values where we expected %.',
                count($values),
                $expectedCount
            )
        );

        $iterator = new VerifyValueCountIterator(
            new \ArrayIterator(
                [$values]
            ),
            $expectedCount
        );

        $iterator->rewind();
        $iterator->current();
    }

    public function getValueCountMismatchTestData()
    {
        return [
            [[123,234], 1],
            [[123,234], 3],
        ];
    }
}
