<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:40 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

use Symfony\Component\EventDispatcher\Event;

class RecordProcessedEvent extends Event
{
    public const NAME = 'slurp.record.processed';
}
