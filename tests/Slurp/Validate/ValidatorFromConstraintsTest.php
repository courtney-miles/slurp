<?php
/**
 * Author: Courtney Miles
 * Date: 13/08/18
 * Time: 9:54 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Validate;

use MilesAsylum\Slurp\PHPUnit\StubValidatorTrait;
use MilesAsylum\Slurp\Validate\ValidatorFromConstraints;
use MilesAsylum\Slurp\Validate\Violation;
use MilesAsylum\Slurp\Validate\ViolationInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorFromConstraintsTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use StubValidatorTrait;

    /**
     * @var ValidatorInterface|MockInterface
     */
    protected $mockValidator;

    /**
     * @var ValidatorFromConstraints
     */
    protected $validator;
    
    public function setUp()
    {
        parent::setUp();
        $this->mockValidator = \Mockery::mock(ValidatorInterface::class);
        $this->validator = new ValidatorFromConstraints($this->mockValidator);
    }

    public function testValidateField()
    {
        $recordId = 123;
        $field = 'col_one';
        $value = 'foo';
        $constraints = [new NotBlank()];
        $message = 'Oops!';

        $mockViolationList = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = \Mockery::mock(ConstraintViolationInterface::class);
        $mockViolation->shouldReceive('getMessage')->andReturn($message);
        $this->stubValidator($value, $constraints, $this->mockValidator, $mockViolationList, [$mockViolation]);

        $this->validator->addColumnConstraints('col_one', $constraints);

        $violations = $this->validator->validateField($recordId, $field, $value);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new Violation($recordId, $field, $value, $message), $violation);
    }

    public function testValidateRecord()
    {
        $recordId = 123;
        $field = 'col_one';
        $value = 'foo';
        $constraints = [new NotBlank()];
        $message = 'Oops!';

        $mockViolationList = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = \Mockery::mock(ConstraintViolationInterface::class);
        $mockViolation->shouldReceive('getMessage')->andReturn($message);
        $this->stubValidator($value, $constraints, $this->mockValidator, $mockViolationList, [$mockViolation]);

        $this->validator->addColumnConstraints('col_one', $constraints);

        $violations = $this->validator->validateRecord($recordId, [$field => $value, 'col_two' => 'bar']);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new Violation($recordId, $field, $value, $message), $violation);
    }
}
