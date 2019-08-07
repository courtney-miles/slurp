<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:16 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\RecordValidatedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class RecordValidatedEventTest extends TestCase
{
    public function testGetPayload(): void
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new RecordValidatedEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
