<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:53 PM
 */

namespace MilesAsylum\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class StrCaseTransformer extends AbstractChangeTransformer
{
    public function transform($value, Change $change)
    {
        if (!$change instanceof StrCase) {
            throw UnexpectedTypeException::createUnexpected($change, StrCase::class);
        }

        if (!$this->isString($value)) {
            throw UnexpectedTypeException::createUnexpected($value, 'string');
        }

        switch ($change->getCaseChange()) {
            case StrCase::CASE_UPPER:
                $value = strtoupper($value);
                break;
            case StrCase::CASE_LOWER:
                $value = strtolower($value);
                break;
        }

        return $value;
    }
}
