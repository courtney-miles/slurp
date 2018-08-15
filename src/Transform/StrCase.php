<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:52 PM
 */

namespace MilesAsylum\Slurp\Transform;

class StrCase extends Change
{
    private $caseChange;

    const CASE_UPPER = 'upper';
    const CASE_LOWER = 'lower';

    public function __construct($caseChange)
    {
        $this->caseChange = $caseChange;
    }

    public function transformedBy()
    {
        return StrCaseTransformer::class;
    }

    public function getCaseChange()
    {
        return $this->caseChange;
    }
}
