<?php
/**
 * Author: Courtney Miles
 * Date: 2/12/18
 * Time: 9:02 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackChange;
use MilesAsylum\Slurp\Transform\SlurpTransformer\CallbackTransformer;
use PHPUnit\Framework\TestCase;

class CallbackChangeTest extends TestCase
{
    public function testTransformedBy()
    {
        $change = new CallbackChange(function () {
        });

        $this->assertSame(CallbackTransformer::class, $change->transformedBy());
    }

    public function testInvoke()
    {
        $change = new CallbackChange(function ($value) {
            return $value;
        });

        $this->assertSame('foo', $change('foo'));
    }
}
