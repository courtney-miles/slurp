<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:53 PM
 */

namespace MilesAsylum\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class StrCaseTransformer extends AbstractTransformer
{
    public function transform($value, Change $transformation)
    {
        if (!$transformation instanceof StrCase) {
            throw new UnexpectedTypeException($transformation, StrCase::class);
        }

        if (!$this->isString($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        switch ($transformation->getCaseChange()) {
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
