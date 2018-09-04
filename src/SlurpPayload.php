<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:45 PM
 */

namespace MilesAsylum\Slurp;

use MilesAsylum\Slurp\Validate\Violation;
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
     * @var Violation[]
     */
    protected $violations = [];

    /**
     * @return int
     */
    public function getRowId():? int
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

    /**
     * @param $name
     * @return mixed|null
     */
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
     * @return Violation[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function valueHasViolation($valueName): bool
    {
        if (!$this->hasViolations()) {
            return false;
        }

        foreach ($this->violations as $violation) {
            if ($violation->getField() == $valueName) {
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

    public function addViolation(Violation $violation): void
    {
        $this->violations[] = $violation;
    }

    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }
}
