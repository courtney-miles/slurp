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

namespace MilesAsylum\Slurp\Extract\Exception;

class ValueCountMismatchException extends MalformedSourceException
{
    public static function createMismatch($recordId, int $givenValueCount, int $expectedValueCount): self
    {
        return new static(
            sprintf(
                'Record %s contained %s values where we expected %s.',
                $recordId,
                $givenValueCount,
                $expectedValueCount
            )
        );
    }
}
