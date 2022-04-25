<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Extract\Exception;

class DuplicatePrimaryKeyValueException extends ExtractionException
{
    /**
     * @param string[] $pkFields
     */
    public static function create(array $pkFields, array $pkValues, int $recordNum): self
    {
        return new self(
            sprintf(
                "Duplicate value '%s' found for primary key %s in record number %s.",
                implode(':', $pkValues),
                implode(':', $pkFields),
                $recordNum
            )
        );
    }
}
