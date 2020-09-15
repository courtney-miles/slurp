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

    public static function createTransformer(): self
    {
        return new self(new TransformerLoader());
    }

    /**
     * @param Change|Change[] $changes
     */
    public function setFieldChanges(string $field, $changes): void
    {
        unset($this->fieldChanges[$field]);

        if (!is_array($changes)) {
            $changes = [$changes];
        }

        foreach ($changes as $change) {
            $this->addFieldChange($field, $change);
        }
    }

    public function addFieldChange(string $field, Change $change): void
    {
        if (!$change instanceof Change) {
            throw UnexpectedTypeException::createUnexpected($change, Change::class);
        }

        $this->fieldChanges[$field][] = $change;
    }

    public function transformField(string $field, $value)
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
                trigger_error(
                    sprintf(
                        'Unable to apply transformation for field \'%s\'. The supplied record did not contain this field.',
                        $field
                    ),
                    E_USER_WARNING
                );
                continue;
            }

            foreach ($changes as $change) {
                $record[$field] = $this->loader->loadTransformer($change)
                    ->transform($record[$field], $change);
            }
        }

        return $record;
    }
}
