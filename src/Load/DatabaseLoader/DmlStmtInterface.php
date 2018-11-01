<?php
/**
 * Author: Courtney Miles
 * Date: 22/09/18
 * Time: 6:26 AM
 */

namespace MilesAsylum\Slurp\Load\DatabaseLoader;


/**
 * This class can been used to run a DML statement immediately prior to committing the loaded records.
 */
interface DmlStmtInterface
{
    /**
     * @return int The number of affected rows.
     */
    public function execute(): int;
}
