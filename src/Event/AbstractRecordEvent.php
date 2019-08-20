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
