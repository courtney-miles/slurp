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

namespace MilesAsylum\Slurp\Load\Exception;

use MilesAsylum\Slurp\Exception\ExceptionInterface;

class MissingValueException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function createMissing(int $recordId, array $missingFields): self
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
