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

namespace MilesAsylum\Slurp\Tests\Slurp\Validate\ConstraintValidation;

use MilesAsylum\Slurp\PHPUnit\StubValidatorTrait;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\ViolationInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use StubValidatorTrait;

    /**
     * @var ValidatorInterface|MockInterface
     */
    protected $mockValidator;

    /**
     * @var ConstraintValidator
     */
    protected $validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockValidator = Mockery::mock(ValidatorInterface::class);
        $this->validator = new ConstraintValidator($this->mockValidator);
    }

    public function testValidateField(): void
    {
        $recordId = 123;
        $field = 'col_one';
        $value = 'foo';
        $constraints = [new NotBlank()];
        $message = 'Oops!';

        $mockViolationList = Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = Mockery::mock(ConstraintViolationInterface::class);
        $mockViolation->shouldReceive('getMessage')->andReturn($message);
        $this->stubValidator($value, $constraints, $this->mockValidator, $mockViolationList, [$mockViolation]);

        $this->validator->setFieldConstraints('col_one', $constraints);

        $violations = $this->validator->validateField($recordId, $field, $value);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new FieldViolation($recordId, $field, $value, $message), $violation);
    }

    public function testValidateRecord(): void
    {
        $recordId = 123;
        $field = 'col_one';
        $value = 'foo';
        $constraints = [new NotBlank()];
        $message = 'Oops!';

        $mockViolationList = Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = Mockery::mock(ConstraintViolationInterface::class);
        $mockViolation->shouldReceive('getMessage')->andReturn($message);
        $this->stubValidator($value, $constraints, $this->mockValidator, $mockViolationList, [$mockViolation]);

        $this->validator->setFieldConstraints('col_one', $constraints);

        $violations = $this->validator->validateRecord($recordId, [$field => $value, 'col_two' => 'bar']);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new FieldViolation($recordId, $field, $value, $message), $violation);
    }
}
