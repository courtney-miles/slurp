<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 6:52 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

class RecordValidatedEvent extends AbstractRecordEvent
{
    public const NAME = 'slurp.record.validated';
}
