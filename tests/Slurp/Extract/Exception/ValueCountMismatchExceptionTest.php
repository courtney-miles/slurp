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

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\Exception;

use MilesAsylum\Slurp\Extract\Exception\ValueCountMismatchException;
use PHPUnit\Framework\TestCase;

class ValueCountMismatchExceptionTest extends TestCase
{
    public function testCreateMismatch(): void
    {
        $e = ValueCountMismatchException::createMismatch(123, 1, 2);

        $this->assertSame(
            'Record 123 contained 1 values where we expected 2.',
            $e->getMessage()
        );
    }
}
