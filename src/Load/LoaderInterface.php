<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 7:06 PM
 */

namespace MilesAsylum\Slurp\Load;

interface LoaderInterface
{
    public function loadRow(array $row) : void;

    public function finalise() : void;
}
