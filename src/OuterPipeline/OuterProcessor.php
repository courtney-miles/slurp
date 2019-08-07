<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:45 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\OuterPipeline;

use League\Pipeline\InterruptibleProcessor;
use MilesAsylum\Slurp\Slurp;

class OuterProcessor extends InterruptibleProcessor
{
    public function __construct()
    {
        parent::__construct(
            static function (Slurp $slurp) {
                return !$slurp->isAborted();
            }
        );
    }
}
