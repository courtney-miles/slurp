<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:32 PM
 */

namespace MilesAsylum\Slurp\Extract;


interface ExtractorInterface extends \Iterator
{
    /**
     * Get the defined columns for the source.
     * @return array
     */
    public function getColumnNames();
}