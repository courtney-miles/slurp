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

namespace MilesAsylum\Slurp\Tests\Slurp\Event;

use MilesAsylum\Slurp\Event\ExtractionAbortedEvent;
use PHPUnit\Framework\TestCase;

class ExtractionAbortedEventTest extends TestCase
{
    public function testDefaultReason(): void
    {
        $event = new ExtractionAbortedEvent();

        $this->assertNull($event->getReason());
    }

    public function testDefaultRecordId(): void
    {
        $event = new ExtractionAbortedEvent();

        $this->assertNull($event->getRecordId());
    }

    public function testGetReason(): void
    {
        $reason = 'foo';
        $event = new ExtractionAbortedEvent($reason);

        $this->assertSame($reason, $event->getReason());
    }

    public function testGetRecordId(): void
    {
        $recordId = 123;
        $event = new ExtractionAbortedEvent(null, $recordId);

        $this->assertSame($recordId, $event->getRecordId());
    }
}
