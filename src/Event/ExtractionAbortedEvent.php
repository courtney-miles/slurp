<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:38 PM
 */

namespace MilesAsylum\Slurp\Event;


use Symfony\Component\EventDispatcher\Event;

class ExtractionAbortedEvent extends Event
{
    public const NAME = 'slurp.extraction.aborted';
}
