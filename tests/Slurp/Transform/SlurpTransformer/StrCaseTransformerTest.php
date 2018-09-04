<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 9:42 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use MilesAsylum\Slurp\Transform\SlurpTransformer\StrCase;
use MilesAsylum\Slurp\Transform\SlurpTransformer\StrCaseTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Transform\SlurpTransformer\StrCaseTransformer
 * @package MilesAsylum\Slurp\Tests\Slurp\Transform
 */
class StrCaseTransformerTest extends TestCase
{
    /**
     * @dataProvider getTestValues
     * @param $caseChange
     * @param $value
     * @param $expectedResult
     */
    public function testTransform($caseChange, $value, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            (new StrCaseTransformer())->transform($value, new StrCase($caseChange))
        );
    }

    public function getTestValues()
    {
        return [
            [StrCase::CASE_UPPER, 'foo', 'FOO'],
            [StrCase::CASE_UPPER, 123, '123'],
            [StrCase::CASE_UPPER, \Mockery::mock(\stdClass::class, ['__toString' => 'foo']), 'FOO'],
            [StrCase::CASE_LOWER, 'FOO', 'foo'],
            [StrCase::CASE_LOWER, 123, '123'],
            [StrCase::CASE_LOWER, \Mockery::mock(\stdClass::class, ['__toString' => 'FOO']), 'foo']
        ];
    }

    public function testExceptionOnUnexpectedTransformationType()
    {
        $this->expectException(UnexpectedTypeException::class);

        (new StrCaseTransformer())->transform('Foo123Bar', \Mockery::mock(Change::class));
    }

    /**
     * @dataProvider getInvalidValueTestData
     * @param $invalidValue
     */
    public function testExceptionOnUnexpectedValueType($invalidValue)
    {
        $this->expectException(UnexpectedTypeException::class);

        (new StrCaseTransformer())->transform($invalidValue, new StrCase(StrCase::CASE_LOWER));
    }

    public function getInvalidValueTestData()
    {
        return [
            [[]],
            [new \stdClass()]
        ];
    }
}
