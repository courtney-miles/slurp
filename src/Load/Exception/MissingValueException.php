<?php
/**
 * Author: Courtney Miles
 * Date: 20/08/18
 * Time: 10:57 PM
 */

namespace MilesAsylum\Slurp\Load\Exception;

use MilesAsylum\Slurp\Exception\ExceptionInterface;

class MissingValueException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function createMissing($recordId, array $missingFields): self
    {
        return new static(
            sprintf(
                'Record %s is missing values for the following fields: %s.',
                $recordId,
                implode(', ', $missingFields)
            )
        );
    }
}
