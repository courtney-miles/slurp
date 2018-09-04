<?php
/**
 * Author: Courtney Miles
 * Date: 4/09/18
 * Time: 6:51 AM
 */

namespace MilesAsylum\Slurp\PHPUnit;

use Mockery\MockInterface;

trait StubValidatorTrait
{
    protected function stubValidator(
        $value,
        $constraints,
        MockInterface $mockValidator,
        MockInterface $mockViolationList,
        array $violations
    ): void {
        $this->stubViolationList($mockViolationList, $violations);
        $mockValidator->shouldReceive('validate')
            ->with($value, $constraints)
            ->andReturn($mockViolationList);
    }

    protected function stubViolationList(MockInterface $mockViolationList, array $violations): void
    {
        $arrayIterator = new \ArrayIterator($violations);

        $mockViolationList->shouldReceive('count')
            ->andReturn(count($violations));

        $mockViolationList->shouldReceive('getIterator')
            ->andReturn($arrayIterator);
    }
}
