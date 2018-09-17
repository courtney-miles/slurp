<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 7:06 PM
 */

namespace MilesAsylum\Slurp\Load;

interface LoaderInterface
{
    public function loadValues(array $values) : void;

    public function begin(): void;

    public function hasBegun(): bool;

    public function abort(): void;

    public function isAborted(): bool;

    public function finalise(): void;
}
