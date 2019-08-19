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

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\Exception;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use PHPUnit\Framework\TestCase;
use stdClass;

class UnexpectedTypeExceptionTest extends TestCase
{
    /**
     * @dataProvider getConstructorArgs
     * @param $value
     * @param $expectedType
     * @param $expectedMessage
     */
    public function testConstructMessage($value, $expectedType, $expectedMessage): void
    {
        $e = UnexpectedTypeException::createUnexpected($value, $expectedType);

        $this->assertSame($expectedMessage, $e->getMessage());
    }

    public function getConstructorArgs(): array
    {
        return [
            ['foo', stdClass::class, 'Expected argument of type "stdClass", "string" given'],
            [['foo'], stdClass::class, 'Expected argument of type "stdClass", "array" given'],
            [new stdClass(), stdClass::class, 'Expected argument of type "stdClass", "stdClass" given'],
        ];
    }
}
