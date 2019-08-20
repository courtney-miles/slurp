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

use MilesAsylum\Slurp\Event\LoadAbortedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use PHPUnit\Framework\TestCase;

class LoadAbortedEventTest extends TestCase
{
    public function testGetPayloadDefaultValue(): void
    {
        $event = new LoadAbortedEvent();

        $this->assertNull($event->getPayload());
    }

    public function testGetPayload(): void
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $event = new LoadAbortedEvent($mockPayload);

        $this->assertSame($mockPayload, $event->getPayload());
    }
}
