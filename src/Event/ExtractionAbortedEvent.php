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

use Symfony\Component\EventDispatcher\Event;

class ExtractionAbortedEvent extends Event
{
    public const NAME = 'slurp.extraction.aborted';

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var int|null
     */
    private $recordId;

    public function __construct(string $reason = null, int $recordId = null)
    {
        $this->reason = $reason;
        $this->recordId = $recordId;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return int|null
     */
    public function getRecordId(): ?int
    {
        return $this->recordId;
    }
}
