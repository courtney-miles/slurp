<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ExtractionFinalisationBeginEvent extends Event
{
    public const NAME = 'slurp.extraction.finalisation.begin';
}
