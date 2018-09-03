<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 9:12 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\Exception;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use PHPUnit\Framework\TestCase;

class UnexpectedTypeExceptionTest extends TestCase
{
    /**
     * @dataProvider getConstructorArgs
     * @param $value
     * @param $expectedType
     * @param $expectedMessage
     */
    public function testConstructMessage($value, $expectedType, $expectedMessage)
    {
        $e = UnexpectedTypeException::createUnexpected($value, $expectedType);

        $this->assertSame($expectedMessage, $e->getMessage());
    }

    public function getConstructorArgs()
    {
        return [
            ['foo', \stdClass::class, 'Expected argument of type "stdClass", "string" given'],
            [['foo'], \stdClass::class, 'Expected argument of type "stdClass", "array" given'],
            [new \stdClass(), \stdClass::class, 'Expected argument of type "stdClass", "stdClass" given'],
        ];
    }
}
