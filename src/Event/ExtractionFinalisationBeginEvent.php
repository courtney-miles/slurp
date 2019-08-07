<?php
/**
 * Author: Courtney Miles
 * Date: 19/02/19
 * Time: 5:39 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

use Symfony\Component\EventDispatcher\Event;

class ExtractionFinalisationBeginEvent extends Event
{
    public const NAME = 'slurp.extraction.finalisation.begin';
}
