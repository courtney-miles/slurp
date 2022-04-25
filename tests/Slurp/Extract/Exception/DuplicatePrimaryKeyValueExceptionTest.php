<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Extract\Exception;

use MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Extract\Exception\DuplicatePrimaryKeyValueException
 */
class DuplicatePrimaryKeyValueExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        self::assertSame(
            'Duplicate value \'123:abc\' found for primary key foo:bar in record number 234.',
            DuplicatePrimaryKeyValueException::create(['foo', 'bar'], [123, 'abc'], 234)
                ->getMessage()
        );
    }
}
