<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 11:22 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

class DateTimeFormat extends Change
{
    private $formatFrom;

    private $formatTo;

    public function __construct($formatFrom, $formatTo)
    {
        $this->formatFrom = $formatFrom;
        $this->formatTo = $formatTo;
    }

    /**
     * @return string
     */
    public function getFormatFrom()
    {
        return $this->formatFrom;
    }

    /**
     * @return string
     */
    public function getFormatTo()
    {
        return $this->formatTo;
    }

    /**
     * @return string
     */
    public function transformedBy()
    {
        return DateTimeFormatTransformer::class;
    }
}
