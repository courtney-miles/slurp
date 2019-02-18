<?php
/**
 * Author: Courtney Miles
 * Date: 17/02/19
 * Time: 6:45 PM
 */

namespace MilesAsylum\Slurp\Event;

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\EventDispatcher\Event;

class AbstractRecordEvent extends Event
{
    /**
     * @var SlurpPayload
     */
    private $payload;

    public function __construct(SlurpPayload $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return SlurpPayload
     */
    public function getPayload(): SlurpPayload
    {
        return $this->payload;
    }
}
