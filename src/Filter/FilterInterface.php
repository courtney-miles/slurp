<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 8:48 PM
 */

namespace MilesAsylum\Slurp\Filter;

interface FilterInterface
{
    public function filterRecord(array $record): bool;
}