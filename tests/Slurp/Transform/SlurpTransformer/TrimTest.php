<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:52 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Trim;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TrimTransformer;
use PHPUnit\Framework\TestCase;

class TrimTest extends TestCase
{
    protected const FROM_RIGHT_DEFAULT = true;
    protected const FROM_LEFT_DEFAULT = true;
    protected const CHARS_DEFAULT = " \t\n\r\0\x0B";

    public function testDefaultFromRight()
    {
        $this->assertSame(
            self::FROM_RIGHT_DEFAULT,
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
        $this->assertSame(
            self::FROM_LEFT_DEFAULT,
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
            self::CHARS_DEFAULT,
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

    public function testCreateFromOptionsAllDefaults()
    {
        $change = Trim::createFromOptions([]);

        $this->assertSame(self::FROM_RIGHT_DEFAULT, $change->fromRight());
        $this->assertSame(self::FROM_LEFT_DEFAULT, $change->fromLeft());
        $this->assertSame(self::CHARS_DEFAULT, $change->getChars());
    }

    public function testCreateFromOptions()
    {
        $options = ['fromRight' => false, 'fromLeft' => false, 'chars' => '#$%'];

        $change = Trim::createFromOptions($options);

        $this->assertSame($options['fromRight'], $change->fromRight());
        $this->assertSame($options['fromLeft'], $change->fromLeft());
        $this->assertSame($options['chars'], $change->getChars());
    }
}
