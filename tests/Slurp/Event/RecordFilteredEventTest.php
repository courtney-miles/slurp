<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:14 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\RecordFilteredEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class RecordFilteredEventTest extends TestCase
{
    public function testGetPayload()
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new RecordFilteredEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
