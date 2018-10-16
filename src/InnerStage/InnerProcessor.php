<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 9:57 PM
 */

namespace MilesAsylum\Slurp\InnerStage;

use League\Pipeline\InterruptibleProcessor;
use MilesAsylum\Slurp\SlurpPayload;

class InnerProcessor extends InterruptibleProcessor
{
    public function __construct()
    {
        parent::__construct(
            function (SlurpPayload $payload) {
                return !$payload->isFiltered();
            }
        );
    }
}
