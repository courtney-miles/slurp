<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 7:19 PM
 */

namespace MilesAsylum\Slurp\Validate\ConstraintValidation;

use MilesAsylum\Slurp\Validate\ValidatorInterface;
use MilesAsylum\Slurp\Validate\Violation;
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
     * @param $field
     * @param Constraint|Constraint[] $constraints
     */
    public function addColumnConstraints($field, $constraints): void
    {
        $this->fieldConstraints[$field] = $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public function validateField(int $recordId, string $field, $value): array
    {
        $violations = [];

        if (isset($this->fieldConstraints[$field])) {
            $constraintViolations = $this->validator->validate($value, $this->fieldConstraints[$field]);

            /** @var ConstraintViolationInterface $constraintViolation */
            foreach ($constraintViolations as $constraintViolation) {
                $violations[] = new Violation($recordId, $field, $value, $constraintViolation->getMessage());
            }
        }

        return $violations;
    }

    /**
     * {@inheritdoc}
     */
    public function validateRecord(int $recordId, array $record): array
    {
        $violations = [];

        foreach ($record as $field => $value) {
            $violations = array_merge($violations, $this->validateField($recordId, $field, $value));
        }

        return $violations;
    }
}
