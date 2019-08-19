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

class FieldViolation implements ViolationInterface
{
    /**
     * @var int
     */
    private $recordId;

    /**
     * @var string
     */
    private $field;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $message;

    public function __construct(int $recordId, string $field, $value, string $message)
    {
        $this->recordId = $recordId;
        $this->field = $field;
        $this->value = $value;
        $this->message = $message;
    }

    public function getRecordId(): int
    {
        return $this->recordId;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
