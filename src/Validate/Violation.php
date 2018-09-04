<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:52 PM
 */

namespace MilesAsylum\Slurp\Validate;

class Violation implements ViolationInterface
{
    private $recordId;
    private $field;
    private $value;

    /**
     * @var string
     */
    private $message;

    public function __construct($recordId, $field, $value, string $message)
    {
        $this->recordId = $recordId;
        $this->field = $field;
        $this->value = $value;
        $this->message = $message;
    }

    public function getRecordId()
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
