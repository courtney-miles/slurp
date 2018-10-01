<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:46 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\Slurp;

interface OuterStageInterface
{
    const STATE_BEGIN = 'begin';
    const STATE_END = 'end';

    public function __invoke(Slurp $slurp): Slurp;

    public function attachObserver(OuterStageObserverInterface $observer): void;

    public function getState(): string;
}
