<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:48 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Validate;

interface ViolationInterface
{
    public function getRecordId(): int;

    public function getMessage(): string;
}
