<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 8:51 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Filter\ConstraintFiltration;

use MilesAsylum\Slurp\Filter\FilterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintFilter implements FilterInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Constraint[][];
     */
    private $fieldConstraints = [];

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function setFieldConstraints(string $field, $constraints): void
    {
        $this->fieldConstraints[$field] = $constraints;
    }

    public function filterRecord(array $record): bool
    {
        foreach ($record as $field => $value) {
            if ($this->fieldFilterMatch($field, $value)) {
                return true;
            }
        }

        return false;
    }

    protected function fieldFilterMatch(string $field, $value): bool
    {
        if (!isset($this->fieldConstraints[$field])) {
            return false;
        }

        // Where there are no violations, this means the value is a valid match and should be filtered.
        return !(bool)count($this->validator->validate($value, $this->fieldConstraints[$field]));
    }
}
