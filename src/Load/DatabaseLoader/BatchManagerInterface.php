<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 8:52 AM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Load\DatabaseLoader;

use MilesAsylum\Slurp\Load\Exception\LoadRuntimeException;

interface BatchManagerInterface
{
    /**
     * @param array $rows
     * @throws LoadRuntimeException Thrown if an issue occurs persisting rows to storage.
     */
    public function write(array $rows): void;
}
