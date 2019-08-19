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

namespace MilesAsylum\Slurp\Transform;

interface TransformerInterface
{
    public function transformField(string $field, $value);

    public function transformRecord(array $record): array;
}
