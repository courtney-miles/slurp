<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:33 PM
 */

namespace MilesAsylum\Slurp\Event;


use Symfony\Component\EventDispatcher\Event;

class ExtractionEndedEvent extends Event
{
    public const NAME = 'slurp.extraction.ended';
}
