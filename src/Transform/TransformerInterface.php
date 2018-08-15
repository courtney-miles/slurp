<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:53 PM
 */

namespace MilesAsylum\Slurp\Transform;

interface TransformerInterface
{
    public function transform($value, Change $transformation);
}