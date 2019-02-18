<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:18 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\RecordTransformedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class RecordTransformedEventTest extends TestCase
{
    public function testGetPayload()
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new RecordTransformedEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
