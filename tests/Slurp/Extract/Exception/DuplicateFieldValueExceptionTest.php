<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\Exception;

use MilesAsylum\Slurp\Extract\Exception\DuplicateFieldValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Extract\Exception\DuplicateFieldValueException
 */
class DuplicateFieldValueExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        self::assertSame(
            'Duplicate value \'123\' found for field foo in record number 234.',
            DuplicateFieldValueException::create('foo', 123, 234)
                ->getMessage()
        );
    }
}
