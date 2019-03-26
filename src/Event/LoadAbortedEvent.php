<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 7:24 PM
 */

namespace MilesAsylum\Slurp\Event;

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\EventDispatcher\Event;

class LoadAbortedEvent extends Event
{
    /**
     * @var SlurpPayload|null
     */
    private $payload;

    public const NAME = 'slurp.load.aborted';

    public function __construct(SlurpPayload $payload = null)
    {
        $this->payload = $payload;
    }

    /**
     * The payload that caused the load to be aborted.
     * @return SlurpPayload|null
     */
    public function getPayload():? SlurpPayload
    {
        return $this->payload;
    }
}
