<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 6:54 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

class RecordLoadedEvent extends AbstractRecordEvent
{
    public const NAME = 'slurp.record.loaded';
}
