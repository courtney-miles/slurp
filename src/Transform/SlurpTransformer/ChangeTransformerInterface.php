<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:53 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

interface ChangeTransformerInterface
{
    public function transform($value, Change $change);
}
