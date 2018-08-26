<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:46 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\Slurp;

interface OuterProcessStageInterface
{
    public function __invoke(Slurp $slurp): Slurp;
}
