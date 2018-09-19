<?php
/**
 * Author: Courtney Miles
 * Date: 4/09/18
 * Time: 7:57 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Validate\SchemaValidation;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use MilesAsylum\Slurp\Validate\Violation;
use MilesAsylum\Slurp\Validate\ViolationInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class SchemaValidatorTest extends TestCase
{
    /**
     * @var SchemaValidator
     */
    protected $validator;

    /**
     * @var Schema|MockInterface
     */
    protected $mockTableSchema;

    public function setUp()
    {
        $this->mockTableSchema = \Mockery::mock(Schema::class);
        $this->validator = new SchemaValidator($this->mockTableSchema);
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
        $this->stubSchemaValidationFieldError($field, $value, $this->mockTableSchema, [$mockValidationError]);

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

        $record = [$badField => $badValue, $goodField => $goodValue];

        $mockValidationError = \Mockery::mock(SchemaValidationError::class);
        $mockValidationError->extraDetails = [
            'field' => $badField,
            'value' => $badValue
        ];
        $mockValidationError->shouldReceive('getMessage')
            ->andReturn($message);
        $this->stubSchemaValidationRecordError($record, $this->mockTableSchema, [$mockValidationError]);

        $violations = $this->validator->validateRecord($recordId, $record);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new Violation($recordId, $badField, $badValue, $message), $violation);
    }

    public function testValidatRecordWithUniqueField()
    {
        $field = 'id';
        $record = [$field => 123];

        $mockUniqueField = \Mockery::mock(BaseField::class);
        $mockUniqueField->shouldReceive('name')
            ->andReturn($field);
        $mockUniqueField->shouldReceive('unique')
            ->withNoArgs()
            ->andReturn(true);

        $this->stubSchemaValidationRecordError($record, $this->mockTableSchema, [], [$mockUniqueField]);

        $this->validator->validateRecord(1, $record);
        $violations = $this->validator->validateRecord(2, $record);

        $this->assertInternalType('array', $violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new Violation(2, $field, $record[$field], "Field value is not unique."), $violation);
    }

    public function testUnknownFieldException()
    {
        $this->expectException(UnknownFieldException::class);
        $this->expectExceptionMessage('Unknown field foo.');

        $field = 'foo';

        $this->mockTableSchema->shouldReceive('field')
            ->with($field)
            ->andThrow(\Exception::class);

        $this->validator->validateField(123, $field, 234);
    }

    protected function stubSchemaValidationFieldError(
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

    protected function stubSchemaValidationRecordError(
        $record,
        MockInterface $mockTableSchema,
        array $validationErrors,
        array $fields = []
    ) {
        $mockTableSchema->shouldReceive('validateRow')
            ->with($record)
            ->andReturn($validationErrors);
        $mockTableSchema->shouldReceive('fields')
            ->andReturn($fields);
    }
}
