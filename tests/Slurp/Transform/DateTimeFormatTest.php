<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:27 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform;

use MilesAsylum\Slurp\Transform\DateTimeFormat;
use MilesAsylum\Slurp\Transform\DateTimeFormatTransformer;
use PHPUnit\Framework\TestCase;

class DateTimeFormatTest extends TestCase
{
    public function testGetFormatFrom()
    {
        $this->assertSame(
            'Y-m-d',
            (new DateTimeFormat('Y-m-d', 'Y'))->getFormatFrom()
        );
    }

    public function testGetFormatTo()
    {
        $this->assertSame(
            'Y-m-d',
            (new DateTimeFormat('Y', 'Y-m-d'))->getFormatTo()
        );
    }

    public function testTransformedBy()
    {
        $this->assertSame(
            DateTimeFormatTransformer::class,
            (new DateTimeFormat('Y', 'Y'))->transformedBy()
        );
    }
}
