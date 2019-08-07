<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 11:06 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Transform\Exception;

use MilesAsylum\Slurp\Exception\ExceptionInterface;

class UnexpectedTypeException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function createUnexpected($value, string $expectedType): self
    {
        return new static(
            sprintf(
                'Expected argument of type "%s", "%s" given',
                $expectedType,
                \is_object($value) ? \get_class($value) : \gettype($value)
            )
        );
    }
}
