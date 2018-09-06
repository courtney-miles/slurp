<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:34 PM
 */

namespace MilesAsylum\Slurp\Validate\SchemaValidation;

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

    public function __construct(Schema $tableSchema)
    {
        $this->tableSchema = $tableSchema;
    }

    /**
     * {@inheritdoc}
     */
    public function validateField($recordId, $field, $value): array
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
    public function validateRecord($recordId, array $record): array
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

        return $violations;
    }
}
