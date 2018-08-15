<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:45 AM
 */

namespace MilesAsylum\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class TrimTransformer extends AbstractTransformer
{
    public function transform($value, Change $transformation)
    {
        if (!$transformation instanceof Trim) {
            throw new UnexpectedTypeException($transformation, Trim::class);
        }

        if (!$this->isString($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ($transformation->fromLeft() && $transformation->fromRight()) {
            $value = trim($value, $transformation->getChars());
        } elseif ($transformation->fromLeft()) {
            $value = ltrim($value, $transformation->getChars());
        } elseif ($transformation->fromRight()) {
            $value = rtrim($value, $transformation->getChars());
        }

        return $value;
    }
}
