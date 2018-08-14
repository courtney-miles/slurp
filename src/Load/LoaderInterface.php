<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 7:06 PM
 */

namespace MilesAsylum\Slurp\Load;

use MilesAsylum\Slurp\Slurp;

interface LoaderInterface
{
    public function update(Slurp $slurp);
}