<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:46 PM
 */

namespace MilesAsylum\Slurp\Stage;

interface OuterProcessStageInterface
{
    public function __invoke(): void;
}
