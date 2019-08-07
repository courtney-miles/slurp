<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:57 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\InnerPipeline;

use League\Pipeline\InterruptibleProcessor;
use MilesAsylum\Slurp\SlurpPayload;

class InnerProcessor extends InterruptibleProcessor
{
    public function __construct()
    {
        parent::__construct(
            static function (SlurpPayload $payload) {
                return !$payload->isFiltered();
            }
        );
    }
}
