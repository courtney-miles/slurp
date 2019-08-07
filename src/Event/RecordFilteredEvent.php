<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 6:49 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

class RecordFilteredEvent extends AbstractRecordEvent
{
    public const NAME = 'slurp.record.filtered';
}
