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

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackChange;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use PHPUnit\Framework\TestCase;

class CallbackTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $transformer = new CallbackTransformer();

        $this->assertSame(
            'foo',
            $transformer->transform(
                'foo',
                new CallbackChange(static function ($value) {
                    return $value;
                })
            )
        );
    }

    public function testExceptionOnWrongType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $transformer = new CallbackTransformer();

        $transformer->transform('foo', \Mockery::mock(Change::class));
    }
}
