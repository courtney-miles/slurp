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

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Contracts\EventDispatcher\Event;

class LoadAbortedEvent extends Event
{
    /**
     * @var SlurpPayload|null
     */
    private $payload;

    public const NAME = 'slurp.load.aborted';

    public function __construct(?SlurpPayload $payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * The payload that caused the load to be aborted.
     */
    public function getPayload(): ?SlurpPayload
    {
        return $this->payload;
    }
}
