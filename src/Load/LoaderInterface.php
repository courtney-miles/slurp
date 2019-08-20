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

namespace MilesAsylum\Slurp\Load;

interface LoaderInterface
{
    public function loadRecord(array $record): void;

    public function begin(): void;

    public function hasBegun(): bool;

    public function abort(): void;

    public function isAborted(): bool;

    public function finalise(): void;
}
