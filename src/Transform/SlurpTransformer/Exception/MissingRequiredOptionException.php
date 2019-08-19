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

namespace MilesAsylum\Slurp\Transform\SlurpTransformer\Exception;

use InvalidArgumentException;
use MilesAsylum\Slurp\Exception\ExceptionInterface;

class MissingRequiredOptionException extends InvalidArgumentException implements ExceptionInterface
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
