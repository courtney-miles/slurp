<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:09 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Load\Exception;

use MilesAsylum\Slurp\Load\Exception\MissingValueException;
use PHPUnit\Framework\TestCase;

class MissingValueExceptionTest extends TestCase
{
    public function testCreateMissing()
    {
        $e = MissingValueException::createMissing(123, ['foo', 'bar']);

        $this->assertSame(
            'Record 123 is missing values for the following fields: foo, bar.',
            $e->getMessage()
        );
    }
}
