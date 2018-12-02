<?php
/**
 * Author: Courtney Miles
 * Date: 2/12/18
 * Time: 7:37 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackChange;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use PHPUnit\Framework\TestCase;

class CallbackTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new CallbackTransformer();

        $this->assertSame(
            'foo',
            $transformer->transform(
                'foo',
                new CallbackChange(function ($value) {
                    return $value;
                })
            )
        );
    }

    public function testExceptionOnWrongType()
    {
        $this->expectException(UnexpectedTypeException::class);

        $transformer = new CallbackTransformer();

        $transformer->transform('foo', \Mockery::mock(Change::class));
    }
}
