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

namespace MilesAsylum\Slurp\Validate\SchemaValidation;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use MilesAsylum\Slurp\Validate\ValidatorInterface;

class SchemaValidator implements ValidatorInterface
{
    /**
     * @var Schema
     */
    private $tableSchema;

    /**
     * @var array
     */
    private $uniqueFieldValues = [];

    /**
     * @var array|null
     */
    private $foundUniqueFields;

    /**
     * @var array|null
     */
    private $fieldNames;

    public function __construct(Schema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
    }

    public function validateField(int $recordId, string $field, $value): array
    {
        $violations = [];

        try {
            $schemaField = $this->tableSchema->field($field);
        } catch (\Exception $e) {
            throw new UnknownFieldException($field, 'Unknown field ' . $field . '.');
        }

        $schemaValidationErrors = $schemaField->validateValue($value);

        if (!empty($schemaValidationErrors)) {
            /** @var SchemaValidationError $schemaError */
            foreach ($schemaValidationErrors as $schemaError) {
                $violations[] = new FieldViolation($recordId, $field, $value, $schemaError->getMessage());
            }
        }

        return $violations;
    }

    public function validateRecord(int $recordId, array $record): array
    {
        $violations = [];

        $schemaValidationErrors = $this->tableSchema->validateRow($record);

        foreach ($schemaValidationErrors as $schemaError) {
            $violations[] = new FieldViolation(
                $recordId,
                $schemaError->extraDetails['field'],
                $schemaError->extraDetails['value'],
                $schemaError->getMessage()
            );
        }

        $expectedFields = $this->getFieldNames();

        if (count($missingFields = array_diff($expectedFields, array_keys($record)))) {
            $violations[] = new RecordViolation(
                $recordId,
                sprintf(
                    'Record is missing field/s: %s',
                    implode(', ', $missingFields)
                )
            );
        }

        if (count($extraFields = array_diff(array_keys($record), $expectedFields))) {
            $violations[] = new RecordViolation(
                $recordId,
                sprintf(
                    'Record has extra field/s: %s',
                    implode(', ', $extraFields)
                )
            );
        }

        foreach ($this->getUniqueFields() as $uniqueField) {
            $fieldName = $uniqueField->name();
            $value = $record[$uniqueField->name()];
            $keyValue = is_object($value) ? spl_object_hash($value) : $value;

            if (isset($this->uniqueFieldValues[$fieldName], $this->uniqueFieldValues[$fieldName][$keyValue])
            ) {
                $violations[] = new FieldViolation(
                    $recordId,
                    $fieldName,
                    $value,
                    "$fieldName: value is not unique."
                );
            } else {
                $this->uniqueFieldValues[$fieldName][$keyValue] = true;
            }
        }

        return $violations;
    }

    /**
     * @return BaseField[]
     */
    protected function getUniqueFields(): array
    {
        if (null === $this->foundUniqueFields) {
            $this->foundUniqueFields = [];

            foreach ($this->tableSchema->fields() as $field) {
                if ($field->unique()) {
                    $this->foundUniqueFields[] = $field;
                }
            }
        }

        return $this->foundUniqueFields;
    }

    protected function getFieldNames(): array
    {
        if (null === $this->fieldNames) {
            $this->fieldNames = [];

            foreach ($this->tableSchema->fields() as $field) {
                $this->fieldNames[] = $field->name();
            }
        }

        return $this->fieldNames;
    }
}
