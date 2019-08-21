<?php

declare(strict_types=1);
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

use MilesAsylum\Slurp\Event\ExtractionFailedEvent;
use PHPUnit\Framework\TestCase;

class ExtractionAbortedEventTest extends TestCase
{
    public function testDefaultReason(): void
    {
        $event = new ExtractionFailedEvent();

        $this->assertNull($event->getReason());
    }

    public function testDefaultRecordId(): void
    {
        $event = new ExtractionFailedEvent();

        $this->assertNull($event->getRecordId());
    }

    public function testGetReason(): void
    {
        $reason = 'foo';
        $event = new ExtractionFailedEvent($reason);

        $this->assertSame($reason, $event->getReason());
    }

    public function testGetRecordId(): void
    {
        $recordId = 123;
        $event = new ExtractionFailedEvent(null, $recordId);

        $this->assertSame($recordId, $event->getRecordId());
    }
}
