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

use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;

interface BatchManagerInterface
{
    /**
     * @throws LoadRuntimeException thrown if an issue occurs persisting rows to storage
     */
    public function write(array $rows): void;
}
