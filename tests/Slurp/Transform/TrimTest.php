<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:52 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Trim;
use MilesAsylum\Slurp\Transform\TrimTransformer;
use PHPUnit\Framework\TestCase;

class TrimTest extends TestCase
{
    public function testDefaultFromRight()
    {
        $this->assertTrue(
            (new Trim())->fromRight()
        );
    }

    public function testFromRight()
    {
        $this->assertFalse(
            (new Trim(true, false, 'abc'))->fromRight()
        );
    }

    public function testDefaultFromLeft()
    {
        $this->assertTrue(
            (new Trim())->fromLeft()
        );
    }

    public function testFromLeft()
    {
        $this->assertFalse(
            (new Trim(false, true, 'abc'))->fromLeft()
        );
    }

    public function testDefaultChars()
    {
        $this->assertSame(
            " \t\n\r\0\x0B",
            (new Trim())->getChars()
        );
    }

    public function testGetChars()
    {
        $this->assertSame(
            'abc',
            (new Trim(true, true, 'abc'))->getChars()
        );
    }

    public function testTransformedBy()
    {
        $this->assertSame(
            TrimTransformer::class,
            (new Trim())->transformedBy()
        );
    }
}
