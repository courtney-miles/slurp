<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:45 PM
 */

namespace MilesAsylum\Slurp\OuterPipeline;

use League\Pipeline\InterruptibleProcessor;
use MilesAsylum\Slurp\Slurp;

class OuterProcessor extends InterruptibleProcessor
{
    public function __construct(callable $interrupt = null)
    {
        parent::__construct(
            function (Slurp $slurp) use ($interrupt) {
                if ($interrupt !== null && $interrupt($slurp)) {
                    $slurp->abort();
                }

                return !$slurp->isAborted();
            }
        );
    }
}
