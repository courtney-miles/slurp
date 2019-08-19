<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
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
