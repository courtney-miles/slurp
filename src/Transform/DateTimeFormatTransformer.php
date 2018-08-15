<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:24 AM
 */

namespace MilesAsylum\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class DateTimeFormatTransformer extends AbstractTransformer
{
    public function transform($value, Change $transformation)
    {
        if (!$transformation instanceof DateTimeFormat) {
            throw new UnexpectedTypeException($transformation, DateTimeFormat::class);
        }

        if (!$this->isString($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = \DateTime::createFromFormat($transformation->getFormatFrom(), $value);

        if ($value === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The date value %s was not able to be converted from the format %s',
                    $value,
                    $transformation->getFormatFrom()
                )
            );
        }

        return $value->format($transformation->getFormatTo());
    }
}
