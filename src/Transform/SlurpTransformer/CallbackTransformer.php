<?php
/**
 * Author: Courtney Miles
 * Date: 1/12/18
 * Time: 7:52 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\Exception\UnexpectedTypeException;

class CallbackTransformer extends AbstractChangeTransformer
{
    public function transform($value, Change $change)
    {
        if (!$change instanceof CallbackChange) {
            throw UnexpectedTypeException::createUnexpected($change, CallbackChange::class);
        }

        return $change($value);
    }
}
