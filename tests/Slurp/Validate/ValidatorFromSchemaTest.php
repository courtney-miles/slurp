<?php
/**
 * Author: Courtney Miles
 * Date: 4/09/18
 * Time: 7:57 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Validate;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Validate\ValidatorFromSchema;
use MilesAsylum\Slurp\Validate\Violation;
use MilesAsylum\Slurp\Validate\ViolationInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ValidatorFromSchemaTest extends TestCase
{
    /**
     * @var ValidatorFromSchema
     */
    protected $validator;

    /**
     * @var Schema|MockInterface
     */
    protected $mockTableSchema;

    public function setUp()
    {
        $this->mockTableSchema = \Mockery::mock(Schema::class);
        $this->validator = new ValidatorFromSchema($this->mockTableSchema);
    }

    public function testValidateField()
    {
        $recordId = 123;
        $field = 'foo';
        $value = 'bar';
        $message = 'Oops!';

        $mockValidationError = \Mockery::mock(SchemaValidationError::class);
        $mockValidationError->shouldReceive('getMessage')
            ->andReturn($message);
        $this->stubSchemaValidationError($field, $value, $this->mockTableSchema, [$mockValidationError]);

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
        $badField = 'foo';
        $badValue = 'bar';
        $goodField = 'foo';
        $goodValue = 'bar';
        $message = 'Oops!';

        $mockValidationError = \Mockery::mock(SchemaValidationError::class);
        $mockValidationError->shouldReceive('getMessage')
            ->andReturn($message);
        $this->stubSchemaValidationError($badField, $badValue, $this->mockTableSchema, [$mockValidationError]);
        $this->stubSchemaValidationError($goodField, $goodValue, $this->mockTableSchema, []);

        $violations = $this->validator->validateRecord($recordId, [$badField => $badValue, $goodField => $goodValue]);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new Violation($recordId, $badField, $badValue, $message), $violation);
    }

    protected function stubSchemaValidationError(
        $field,
        $value,
        MockInterface $mockTableSchema,
        array $validationErrors
    ) {
        $mockSchemaField = \Mockery::mock(BaseField::class);
        $mockSchemaField->shouldReceive('validateValue')
            ->with($value)
            ->andReturn($validationErrors);
        $mockTableSchema->shouldReceive('field')
            ->with($field)
            ->andReturn($mockSchemaField);
    }
}
