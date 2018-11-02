<?php
/**
 * Author: Courtney Miles
 * Date: 3/11/18
 * Time: 7:35 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer\Exception;

use MilesAsylum\Slurp\Exception\ExceptionInterface;

class MissingRequiredOptionException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function createMissingOptions(string $changeClass, array $missingOptions): self
    {
        return new self(
            sprintf(
                'The following required options for constructing %s are missing: %s',
                $changeClass,
                implode(', ', $missingOptions)
            )
        );
    }
}
