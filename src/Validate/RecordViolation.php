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

namespace MilesAsylum\Slurp\Validate;

class RecordViolation implements ViolationInterface
{
    /**
     * @var int
     */
    private $recordId;

    /**
     * @var string
     */
    private $message;

    public function __construct(int $recordId, string $message)
    {
        $this->recordId = $recordId;
        $this->message = $message;
    }

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
