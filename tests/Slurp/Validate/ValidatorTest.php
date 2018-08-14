<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 9:54 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Validate;

use MilesAsylum\Slurp\Validate\Validator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ValidatorInterface|MockInterface
     */
    protected $mockValidator;

    /**
     * @var Validator
     */
    protected $validator;
    
    public function setUp()
    {
        parent::setUp();
        $this->mockValidator = \Mockery::mock(ValidatorInterface::class);
        $this->validator = new Validator($this->mockValidator, ['col_one', 'col_two']);
    }

    public function testValidateRow()
    {
        $rowId = 123;
        $constraints = [new NotBlank()];

        $mockContextualValidator = \Mockery::mock(ContextualValidatorInterface::class);
        $mockViolationList = \Mockery::mock(ConstraintViolationListInterface::class);

        $this->mockValidator->shouldReceive('startContext')
            ->with($rowId)
            ->andReturn($mockContextualValidator);
        $mockContextualValidator->shouldReceive('atPath')
            ->with('col_one')
            ->andReturnSelf();
        $mockContextualValidator->shouldReceive('validate')
            ->with('foo', $constraints);
        $mockContextualValidator->shouldReceive('getViolations')
            ->andReturn($mockViolationList);

        $this->validator->addColumnConstraints('col_one', $constraints);
        $this->assertSame($mockViolationList, $this->validator->validateRow(['foo', 'bar'], $rowId));
    }
}
