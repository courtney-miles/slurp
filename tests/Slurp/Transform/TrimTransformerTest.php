<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:10 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Change;
use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use MilesAsylum\Slurp\Transform\Trim;
use MilesAsylum\Slurp\Transform\TrimTransformer;
use PHPUnit\Framework\TestCase;

class TrimTransformerTest extends TestCase
{
    /**
     * @dataProvider getTransformValues
     * @param string $value
     * @param bool $fromLeft
     * @param bool $fromRight
     * @param string $chars
     * @param string $expectedResult
     */
    public function testTransform($value, $fromLeft, $fromRight, $chars, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            (new TrimTransformer())->transform($value, new Trim($fromLeft, $fromRight, $chars))
        );
    }

    public function getTransformValues()
    {
        return [
            [' abc ', true, true, ' ', 'abc'],
            [' abc ', true, false, ' ', 'abc '],
            [' abc ', false, true, ' ', ' abc'],
            [' abc ', false, false, ' ', ' abc '],
            [' abc ', true, true, 'a', ' abc '],
            ['abxyba', true, true, 'ab', 'xy'],
        ];
    }

    public function testExceptionOnUnexpectedTransformationType()
    {
        $this->expectException(UnexpectedTypeException::class);

        (new TrimTransformer())->transform('Foo123Bar', \Mockery::mock(Change::class));
    }

    /**
     * @dataProvider getInvalidValueTestData
     * @param $invalidValue
     */
    public function testExceptionOnUnexpectedValueType($invalidValue)
    {
        $this->expectException(UnexpectedTypeException::class);

        (new TrimTransformer())->transform($invalidValue, new Trim());
    }

    public function getInvalidValueTestData()
    {
        return [
            [[]],
            [new \stdClass()]
        ];
    }
}
