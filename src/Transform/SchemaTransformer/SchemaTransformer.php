<?php
/**
 * Author: Courtney Miles
 * Date: 4/09/18
 * Time: 10:18 PM
 */

namespace MilesAsylum\Slurp\Transform\SchemaTransformer;

use frictionlessdata\tableschema\Schema;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Transform\Exception\TransformationException;
use MilesAsylum\Slurp\Transform\TransformerInterface;

class SchemaTransformer implements TransformerInterface
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
     * @param $field
     * @param $value
     * @return mixed
     * @throws TransformationException
     */
    public function transformField($field, $value)
    {
        try {
            $schemaField = $this->tableSchema->field($field);
        } catch (\Exception $e) {
            throw new UnknownFieldException($field, "Unknown field $field.");
        }

        try {
            $value = $schemaField->castValue($value);
        } catch (\Throwable $e) {
            throw new TransformationException(
                'An error occurred transforming a field:' . $e->getMessage(),
                0,
                $e
            );
        }

        return $value;
    }

    /**
     * @param array $record
     * @return array|mixed[]
     * @throws TransformationException
     */
    public function transformRecord(array $record): array
    {
        try {
            $record = $this->tableSchema->castRow($record);
        } catch (\Throwable $e) {
            throw new TransformationException(
                'An error occurred transforming a record: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return $record;
    }
}
