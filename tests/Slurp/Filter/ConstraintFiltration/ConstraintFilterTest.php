<?php

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp\Filter\ConstraintFiltration;

use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
        $this->mockValidator = \Mockery::mock(ValidatorInterface::class);
        $this->filter = new ConstraintFilter($this->mockValidator);
    }

    public function testDoFilterRecord(): void
    {
        $record = ['foo' => 123, 'bar' => 234];

        $mockConstraint = $this->createMockConstraint();
        $this->mockValidatorValidateExpectations(
            $this->mockValidator,
            234,
            $mockConstraint,
            $this->createMockViolationList([])
        );
        $this->filter->setFieldConstraints('bar', $mockConstraint);

        $this->assertTrue($this->filter->filterRecord($record));
    }

    public function testDoNotFilterRecord(): void
    {
        $record = ['foo' => 123, 'bar' => 234];

        $mockConstraint = $this->createMockConstraint();
        $this->mockValidatorValidateExpectations(
            $this->mockValidator,
            234,
            $mockConstraint,
            $this->createMockViolationList([true]) // ... any non-empty array to satisfy the test.
        );
        $this->filter->setFieldConstraints('bar', $mockConstraint);

        $this->assertFalse($this->filter->filterRecord($record));
    }

    /**
     * @return MockInterface|Constraint
     */
    private function createMockConstraint(): MockInterface
    {
        return \Mockery::mock(Constraint::class);
    }

    private function mockValidatorValidateExpectations(
        MockInterface $mockValidator,
        $validatedValue,
        Constraint $constraint,
        ConstraintViolationListInterface $violationList
    ): void {
        $mockValidator->shouldReceive('validate')
            ->with($validatedValue, $constraint)
            ->andReturn($violationList);
    }

    /**
     * @return MockInterface|ConstraintViolationListInterface
     */
    private function createMockViolationList(array $violations = []): MockInterface
    {
        $mockViolationList = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolationList->shouldReceive('count')
            ->andReturn(count($violations));

        return $mockViolationList;
    }
}
