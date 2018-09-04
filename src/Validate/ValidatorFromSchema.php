<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:34 PM
 */

namespace MilesAsylum\Slurp\Validate;

use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Validate\Exception\UnknownFieldException;

class ValidatorFromSchema implements ValidatorInterface
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
            throw new UnknownFieldException($field, 'Unknown field ' . $field);
        }

        $schemaValidationErrors = $schemaField->validateValue($value);

        if (!empty($schemaValidationErrors)) {
            /** @var SchemaValidationError $validationError */
            foreach ($schemaValidationErrors as $validationError) {
                $violations[] = new Violation($recordId, $field, $value, $validationError->getMessage());
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

        foreach ($record as $field => $value) {
            $violations = array_merge($violations, $this->validateField($recordId, $field, $value));
        }

        return $violations;
    }
}
