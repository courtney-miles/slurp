<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 8:52 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

interface BatchStmtInterface
{
    public function write(array $rows);
}
