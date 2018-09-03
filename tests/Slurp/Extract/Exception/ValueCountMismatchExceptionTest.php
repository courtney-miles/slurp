<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:06 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\Exception;

use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use PHPUnit\Framework\TestCase;

class ValueCountMismatchExceptionTest extends TestCase
{
    public function testCreateMismatch()
    {
        $e = ValueCountMismatchException::createMismatch(123, 1, 2);

        $this->assertSame(
            'Record 123 contained 1 values where we expected 2.',
            $e->getMessage()
        );
    }
}
