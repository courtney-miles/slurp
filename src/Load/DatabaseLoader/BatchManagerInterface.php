<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 8:52 AM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

interface BatchManagerInterface
{
    public function write(array $rows): void;
}
