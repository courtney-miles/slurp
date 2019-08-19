<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

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
