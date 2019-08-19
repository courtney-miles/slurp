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

namespace MilesAsylum\Slurp\Transform\SchemaTransformer;

use Carbon\Carbon;
use Exception;
use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Fields\DateField;
use frictionlessdata\tableschema\Fields\DatetimeField;
use frictionlessdata\tableschema\Fields\TimeField;
use frictionlessdata\tableschema\Schema;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Transform\Exception\TransformationException;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use Throwable;

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
     * @param string $field
     * @param $value
     * @return mixed
     * @throws TransformationException
     */
    public function transformField(string $field, $value)
    {
        try {
            $schemaField = $this->tableSchema->field($field);
        } catch (Exception $e) {
            throw new UnknownFieldException($field, "Unknown field $field.");
        }

        try {
            $value = $schemaField->castValue($value);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            throw new TransformationException(
                'An error occurred transforming a record: ' . $e->getMessage(),
                0,
                $e
            );
        }

        foreach ($record as $fieldName => $value) {
            $field = $this->getField($fieldName);

            if ($field === null || $value === null) {
                continue;
            }

            // Convert complex types back to simple types.
            switch (true) {
                case $field instanceof TimeField:
                    $record[$fieldName] = implode(':', $value);
                    break;
                case $field instanceof DateField:
                    /** @var Carbon $value */
                    $record[$fieldName] = $value->toDateString();
                    break;
                case $field instanceof DatetimeField:
                    /** @var Carbon $value */
                    $record[$fieldName] = $value->toDateTimeString();
                    break;
            }
        }

        return $record;
    }

    /**
     * @param $name
     * @return BaseField|null
     */
    protected function getField($name): ?BaseField
    {
        try {
            $schemaField = $this->tableSchema->field($name);
        } catch (Exception $e) {
            $schemaField = null;
        }

        return $schemaField;
    }
}
