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
