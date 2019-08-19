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

namespace MilesAsylum\Slurp\Tests\Slurp\Filter\ContraintFiltration;

use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ConstraintFilter
     */
    protected $filter;

    /**
     * @var ValidatorInterface|MockInterface
     */
    protected $mockValidator;

    public function setUp(): void
    {
        $this->mockValidator = Mockery::mock(ValidatorInterface::class);
        $this->filter = new ConstraintFilter($this->mockValidator);
    }

    public function testDoFilterRecord(): void
    {
        $record = ['foo' => 123, 'bar' => 234];

        $mockConstraint = Mockery::mock(Constraint::class);
        $this->mockValidator->shouldReceive('validate')
            ->with(234, $mockConstraint)
            ->andReturn([]); // ... a empty array to satisfy the test.
        $this->filter->setFieldConstraints('bar', $mockConstraint);

        $this->assertTrue($this->filter->filterRecord($record));
    }

    public function testDoNotFilterRecord(): void
    {
        $record = ['foo' => 123, 'bar' => 234];

        $mockConstraint = Mockery::mock(Constraint::class);
        $this->mockValidator->shouldReceive('validate')
            ->with(234, $mockConstraint)
            ->andReturn([true]); // ... any non-empty array to satisfy the test.
        $this->filter->setFieldConstraints('bar', $mockConstraint);

        $this->assertFalse($this->filter->filterRecord($record));
    }
}
