<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 10:52 PM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Exception\MissingRequiredOptionException;

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

    public static function createFromOptions(array $options = []): self
    {
        $reqOptions = ['caseChange'];

        if ($missingOptions = array_diff($reqOptions, array_keys($options))) {
            throw MissingRequiredOptionException::createMissingOptions(self::class, $missingOptions);
        }

        return new self($options['caseChange']);
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
