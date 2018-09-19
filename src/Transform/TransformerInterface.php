<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:02 PM
 */

namespace MilesAsylum\Slurp\Transform;

interface TransformerInterface
{
    public function transformField(string $field, $value);

    public function transformRecord(array $record): array;
}
