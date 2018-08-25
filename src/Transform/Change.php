<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 11:03 PM
 */

namespace MilesAsylum\Slurp\Transform;

abstract class Change
{
    /**
     * @return string
     */
    abstract public function transformedBy();
}
