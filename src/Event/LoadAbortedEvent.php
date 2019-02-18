<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 7:24 PM
 */

namespace MilesAsylum\Slurp\Event;

use Symfony\Component\EventDispatcher\Event;

class LoadAbortedEvent extends Event
{
    public const NAME = 'slurp.load.aborted';
}
