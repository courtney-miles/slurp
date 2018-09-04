<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:45 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class TrimTransformer extends AbstractChangeTransformer
{
    public function transform($value, Change $change)
    {
        if (!$change instanceof Trim) {
            throw UnexpectedTypeException::createUnexpected($change, Trim::class);
        }

        if (!$this->isString($value)) {
            throw UnexpectedTypeException::createUnexpected($value, 'string');
        }

        if ($change->fromLeft() && $change->fromRight()) {
            $value = trim($value, $change->getChars());
        } elseif ($change->fromLeft()) {
            $value = ltrim($value, $change->getChars());
        } elseif ($change->fromRight()) {
            $value = rtrim($value, $change->getChars());
        }

        return $value;
    }
}