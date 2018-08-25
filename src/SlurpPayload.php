<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:45 PM
 */

namespace MilesAsylum\Slurp;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SlurpPayload
{
    /**
     * @var int
     */
    protected $rowId;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var ConstraintViolationListInterface|ConstraintViolationInterface[]
     */
    protected $violations;

    /**
     * @return int
     */
    public function getRowId(): ?int
    {
        return $this->rowId;
    }

    /**
     * @param int $rowId
     */
    public function setId(int $rowId): void
    {
        $this->rowId = $rowId;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function getValue($name)
    {
        return isset($this->values[$name]) ? $this->values[$name] : null;
    }

    public function setValue($name, $value): void
    {
        $this->values[$name] = $value;
    }

    public function replaceValue($name, $value): void
    {
        if (!$this->hasValue($name)) {
            throw new \InvalidArgumentException(
                "Unable to replace value for $name. A value does not exists for $name."
            );
        }

        $this->setValue($name, $value);
    }

    public function hasValue($name): bool
    {
        return array_key_exists($name, $this->values);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ?ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function valueHasViolation($valueName)
    {
        if (!$this->hasViolations()) {
            return false;
        }

        foreach ($this->violations as $violation) {
            if ($violation->getPropertyPath() == $valueName) {
                return true;
            }
        }

        return false;
    }

    public function addViolations(ConstraintViolationListInterface $violations): void
    {
        if ($this->violations !== null) {
            $this->violations->addAll($violations);
        } else {
            $this->violations = $violations;
        }
    }

    public function hasViolations()
    {
        if ($this->violations === null) {
            return false;
        }

        return (bool)count($this->violations);
    }
}
