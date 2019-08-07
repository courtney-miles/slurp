<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:25 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

use Symfony\Component\EventDispatcher\Event;

class ExtractionStartedEvent extends Event
{
    public const NAME = 'slurp.extraction.started';
}
