<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:20 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\RecordLoadedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class RecordLoadedEventTest extends TestCase
{
    public function testGetPayload()
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new RecordLoadedEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
