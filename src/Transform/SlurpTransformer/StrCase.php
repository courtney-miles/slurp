<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:52 PM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

class StrCase extends Change
{
    /**
     * @var string
     */
    private $caseChange;

    const CASE_UPPER = 'upper';
    const CASE_LOWER = 'lower';

    public function __construct(string $caseChange)
    {
        $this->caseChange = $caseChange;
    }

    public function transformedBy(): string
    {
        return StrCaseTransformer::class;
    }

    public function getCaseChange(): string
    {
        return $this->caseChange;
    }
}
