<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:02 PM
 */

namespace MilesAsylum\Slurp\Transform;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;

interface TransformerInterface
{
    public function transform($value, Change $change);
}
