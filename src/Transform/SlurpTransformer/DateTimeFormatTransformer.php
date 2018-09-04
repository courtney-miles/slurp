<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:24 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\InvalidArgumentException;
use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class DateTimeFormatTransformer extends AbstractChangeTransformer
{
    public function transform($value, Change $change)
    {
        if (!$change instanceof DateTimeFormat) {
            throw UnexpectedTypeException::createUnexpected($value, DateTimeFormat::class);
        }

        if (!$this->isString($value)) {
            throw UnexpectedTypeException::createUnexpected($value, 'string');
        }

        $value = \DateTime::createFromFormat($change->getFormatFrom(), $value);

        if ($value === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'The date value %s was not able to be converted from the format %s',
                    $value,
                    $change->getFormatFrom()
                )
            );
        }

        return $value->format($change->getFormatTo());
    }
}
