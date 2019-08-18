<?php
/**
 * Author: Courtney Miles
 * Date: 27/08/18
 * Time: 6:10 PM
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
