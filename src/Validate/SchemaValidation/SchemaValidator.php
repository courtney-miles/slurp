<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:34 PM
 */

namespace MilesAsylum\Slurp\Validate\SchemaValidation;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use MilesAsylum\Slurp\Validate\Violation;

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
    private $foundUniqueFields = null;

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
        } catch (\Exception $e) {
            throw new UnknownFieldException($field, 'Unknown field ' . $field . '.');
        }

        $schemaValidationErrors = $schemaField->validateValue($value);

        if (!empty($schemaValidationErrors)) {
            /** @var SchemaValidationError $schemaError */
            foreach ($schemaValidationErrors as $schemaError) {
                $violations[] = new Violation($recordId, $field, $value, $schemaError->getMessage());
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
            $violations[] = new Violation(
                $recordId,
                $schemaError->extraDetails['field'],
                $schemaError->extraDetails['value'],
                $schemaError->getMessage()
            );
        }

        foreach ($this->getUniqueFields($this->tableSchema) as $uniqueField) {
            $fieldName = $uniqueField->name();
            $value = $record[$uniqueField->name()];
            $keyValue = is_object($value) ? spl_object_hash($value) : $value;

            if (isset($this->uniqueFieldValues[$fieldName])
                && isset($this->uniqueFieldValues[$fieldName][$keyValue])
            ) {
                $violations[] = new Violation(
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
     * @param Schema $tableSchema
     * @return BaseField[]
     */
    protected function getUniqueFields(Schema $tableSchema): array
    {
        if ($this->foundUniqueFields === null) {
            $uniqueFields = [];

            foreach ($tableSchema->fields() as $field) {
                if ($field->unique()) {
                    $uniqueFields[] = $field;
                }
            }

            $this->foundUniqueFields = $uniqueFields;
        }

        return $this->foundUniqueFields;
    }
}
