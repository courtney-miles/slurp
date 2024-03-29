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

namespace MilesAsylum\Slurp\Validate\ConstraintValidation;

use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidator;

class ConstraintValidator implements ValidatorInterface
{
    /**
     * @var SymfonyValidator
     */
    private $validator;

    /**
     * @var Constraint[][];
     */
    private $fieldConstraints = [];

    public function __construct(SymfonyValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Constraint|Constraint[] $constraints
     */
    public function setFieldConstraints(string $field, $constraints): void
    {
        $this->fieldConstraints[$field] = $constraints;
    }

    public function validateField(int $recordId, string $field, $value): array
    {
        $violations = [];

        if (isset($this->fieldConstraints[$field])) {
            $constraintViolations = $this->validator->validate($value, $this->fieldConstraints[$field]);

            /** @var ConstraintViolationInterface $constraintViolation */
            foreach ($constraintViolations as $constraintViolation) {
                $violations[] = new FieldViolation($recordId, $field, $value, $constraintViolation->getMessage());
            }
        }

        return $violations;
    }

    public function validateRecord(int $recordId, array $record): array
    {
        $violations = [];

        foreach ($record as $field => $value) {
            $violations = array_merge($violations, $this->validateField($recordId, $field, $value));
        }

        return $violations;
    }
}
