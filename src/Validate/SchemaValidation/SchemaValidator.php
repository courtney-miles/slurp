<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:34 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Validate\SchemaValidation;

use Exception;
use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Validate\RecordViolation;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use MilesAsylum\Slurp\Validate\FieldViolation;

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
     * @var null|array
     */
    private $foundUniqueFields;

    /**
     * @var null|array
     */
    private $fieldNames;

    public function __construct(Schema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
    }

    /**
     * {@inheritdoc}
     */
    public function validateField(int $recordId, string $field, $value): array
    {
        $violations = [];

        try {
            $schemaField = $this->tableSchema->field($field);
        } catch (Exception $e) {
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

    /**
     * {@inheritdoc}
     */
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

            if (isset($this->uniqueFieldValues[$fieldName])
                && isset($this->uniqueFieldValues[$fieldName][$keyValue])
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
        if ($this->foundUniqueFields === null) {
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
        if ($this->fieldNames === null) {
            $this->fieldNames = [];

            foreach ($this->tableSchema->fields() as $field) {
                $this->fieldNames[] = $field->name();
            }
        }

        return $this->fieldNames;
    }
}
