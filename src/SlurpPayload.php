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

namespace MilesAsylum\Slurp;

use MilesAsylum\Slurp\Validate\ViolationInterface;

class SlurpPayload
{
    /**
     * @var int
     */
    protected $recordId;

    /**
     * @var array
     */
    protected $record = [];

    /**
     * @var ViolationInterface[]
     */
    protected $violations = [];

    /**
     * @var bool
     */
    protected $filtered = false;

    protected $loadAborted = false;

    public function getRecordId(): ?int
    {
        return $this->recordId;
    }

    public function setRecordId(int $recordId): void
    {
        $this->recordId = $recordId;
    }

    public function getRecord(): array
    {
        return $this->record;
    }

    public function setRecord(array $record): void
    {
        $this->record = $record;
    }

    /**
     * @param string|int|float $name
     *
     * @return mixed|null
     */
    public function getFieldValue($name)
    {
        return $this->record[$name] ?? null;
    }

    /**
     * @param string|int|float $value
     */
    public function setFieldValue(string $name, $value): void
    {
        $this->record[$name] = $value;
    }

    public function replaceFieldValue(string $name, $value): void
    {
        if (!$this->hasField($name)) {
            throw new \InvalidArgumentException("Unable to replace value for $name. A value does not exists for $name.");
        }

        $this->setFieldValue($name, $value);
    }

    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->record);
    }

    /**
     * @return ViolationInterface[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function fieldHasViolation(string $field): bool
    {
        if (!$this->hasViolations()) {
            return false;
        }

        foreach ($this->violations as $violation) {
            if ($violation->getField() === $field) {
                return true;
            }
        }

        return false;
    }

    public function addViolations(array $violations): void
    {
        foreach ($violations as $violation) {
            $this->addViolation($violation);
        }
    }

    public function addViolation(ViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    public function hasViolations(?string $classType = null): bool
    {
        if (empty($this->violations)) {
            return false;
        }

        if (!empty($this->violations) && null === $classType) {
            return true;
        }

        foreach ($this->violations as $violation) {
            if ($violation instanceof $classType) {
                return true;
            }
        }

        return false;
    }

    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    public function isLoadAborted(): bool
    {
        return $this->loadAborted;
    }

    public function setLoadAborted(bool $loadAborted): void
    {
        $this->loadAborted = $loadAborted;
    }
}
