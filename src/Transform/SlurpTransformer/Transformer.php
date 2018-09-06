<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:27 PM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;
use MilesAsylum\Slurp\Transform\TransformerInterface;

class Transformer implements TransformerInterface
{
    /**
     * @var TransformerLoader
     */
    private $loader;

    /**
     * @var Change[][]
     */
    private $fieldChanges = [];

    public function __construct(TransformerLoader $loader)
    {
        $this->loader = $loader;
    }

    public static function createTransformer()
    {
        return new self(new TransformerLoader());
    }

    public function setFieldChanges($field, $changes)
    {
        unset($this->fieldChanges[$field]);

        if (!is_array($changes)) {
            $changes = [$changes];
        }

        foreach ($changes as $change) {
            if (!$change instanceof Change) {
                throw UnexpectedTypeException::createUnexpected($change, Change::class);
            }

            $this->fieldChanges[$field][] = $change;
        }
    }

    public function transformField($field, $value)
    {
        if (!isset($this->fieldChanges[$field])) {
            throw new UnknownFieldException($field, "Unknown field $field");
        }

        foreach ($this->fieldChanges[$field] as $change) {
            $value = $this->loader->loadTransformer($change)
                ->transform($value, $change);
        }

        return $value;
    }

    public function transformRecord(array $record): array
    {
        foreach ($this->fieldChanges as $field => $changes) {
            if (!isset($record[$field])) {
                return null;
            }

            foreach ($changes as $change) {
                $record[$field] = $this->loader->loadTransformer($change)
                    ->transform($record[$field], $change);
            }
        }

        return $record;
    }
}
