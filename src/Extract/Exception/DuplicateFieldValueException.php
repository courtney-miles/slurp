<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\Exception;

class DuplicateFieldValueException extends ExtractionException
{
    public static function create(string $field, $value, int $recordNum): self
    {
        return new self(
            sprintf(
                "Duplicate value '%s' found for field %s in record number %s.",
                $value,
                $field,
                $recordNum
            )
        );
    }
}
