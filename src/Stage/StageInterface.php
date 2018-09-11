<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:05 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;

interface StageInterface
{
    public function __invoke(SlurpPayload $payload): SlurpPayload;

    public function attachObserver(StageObserverInterface $observer);

    public function getPayload(): SlurpPayload;
}
