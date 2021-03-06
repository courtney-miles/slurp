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

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\RecordFilteredEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class RecordFilteredEventTest extends TestCase
{
    public function testGetPayload(): void
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new RecordFilteredEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
