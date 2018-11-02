<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 9:41 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Exception\MissingRequiredOptionException;
use MilesAsylum\Slurp\Transform\SlurpTransformer\StrCase;
use MilesAsylum\Slurp\Transform\SlurpTransformer\StrCaseTransformer;
use PHPUnit\Framework\TestCase;

class StrCaseTest extends TestCase
{
    public function testGetCaseChange()
    {
        $this->assertSame(
            StrCase::CASE_LOWER,
            (new StrCase(StrCase::CASE_LOWER))->getCaseChange()
        );
    }

    public function testTransformedBy()
    {
        $this->assertSame(
            StrCaseTransformer::class,
            (new StrCase(StrCase::CASE_LOWER))->transformedBy()
        );
    }

    public function testCreateFromOptions()
    {
        $change = StrCase::createFromOptions(['caseChange' => 'upper']);

        $this->assertSame('upper', $change->getCaseChange());
    }

    public function testExceptionOnMissingRequiredOption()
    {
        $this->expectException(MissingRequiredOptionException::class);

        StrCase::createFromOptions([]);
    }
}
