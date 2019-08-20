<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

/**
 * This class can been used to run a DML statement immediately prior to committing the loaded records.
 */
interface DmlStmtInterface
{
    /**
     * @return int the number of affected rows
     */
    public function execute(): int;
}
