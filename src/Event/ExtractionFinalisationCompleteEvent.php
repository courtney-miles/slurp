<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 10:48 PM
 */

namespace MilesAsylum\Slurp\Event;

use Symfony\Component\EventDispatcher\Event;

class ExtractionFinalisationCompleteEvent extends Event
{
    public const NAME = 'slurp.extraction.finalisation.complete';
}
