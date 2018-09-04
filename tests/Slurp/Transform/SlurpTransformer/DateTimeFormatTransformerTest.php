<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:40 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\DateTimeFormat;
use MilesAsylum\Slurp\Transform\SlurpTransformer\DateTimeFormatTransformer;
use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use PHPUnit\Framework\TestCase;

class DateTimeFormatTransformerTest extends TestCase
{
    /**
     * @dataProvider getTransformValues
     * @param $value
     * @param $fromFormat
     * @param $toFormat
     * @param $expectedResult
     */
    public function testTransform($value, $fromFormat, $toFormat, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            (new DateTimeFormatTransformer())->transform($value, new DateTimeFormat($fromFormat, $toFormat))
        );
    }

    public function getTransformValues()
    {
        return [
            ['2018-02-28', 'Y-m-d', 'd-m-Y', '28-02-2018'],
        ];
    }

    public function testExceptionOnUnexpectedTransformationType()
    {
        $this->expectException(UnexpectedTypeException::class);

        (new DateTimeFormatTransformer())->transform('Foo123Bar', \Mockery::mock(Change::class));
    }

    /**
     * @dataProvider getInvalidValueTestData
     * @param $invalidValue
     */
    public function testExceptionOnUnexpectedValueType($invalidValue)
    {
        $this->expectException(UnexpectedTypeException::class);

        (new DateTimeFormatTransformer())->transform($invalidValue, new DateTimeFormat('Y', 'Y'));
    }

    public function getInvalidValueTestData()
    {
        return [
            [[]],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider getInvalidFormatTestData
     * @param $invalidValue
     * @param $fromFormat
     */
    public function testInvalidFormatException($invalidValue, $fromFormat)
    {
        $this->expectException(\InvalidArgumentException::class);

        (new DateTimeFormatTransformer())->transform($invalidValue, new DateTimeFormat($fromFormat, 'Y'));
    }

    public function getInvalidFormatTestData()
    {
        return [
            ['abc', 'Y-m-d']
        ];
    }
}
