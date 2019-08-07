<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 8:48 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Filter;

interface FilterInterface
{
    public function filterRecord(array $record): bool;
}
