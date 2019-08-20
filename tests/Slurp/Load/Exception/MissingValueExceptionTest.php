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

namespace MilesAsylum\Slurp\Tests\Slurp\Load\Exception;

use MilesAsylum\Slurp\Load\Exception\MissingValueException;
use PHPUnit\Framework\TestCase;

class MissingValueExceptionTest extends TestCase
{
    public function testCreateMissing(): void
    {
        $e = MissingValueException::createMissing(123, ['foo', 'bar']);

        $this->assertSame(
            'Record 123 is missing values for the following fields: foo, bar.',
            $e->getMessage()
        );
    }
}
