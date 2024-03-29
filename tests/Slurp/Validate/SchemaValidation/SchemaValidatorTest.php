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

namespace MilesAsylum\Slurp\Tests\Slurp\Validate\SchemaValidation;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Schema;
use frictionlessdata\tableschema\SchemaValidationError;
use MilesAsylum\Slurp\Exception\UnknownFieldException;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
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

    public function setUp(): void
    {
        $this->mockTableSchema = \Mockery::mock(Schema::class);
        $this->validator = new SchemaValidator($this->mockTableSchema);
    }

    public function testValidateField(): void
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

        $this->assertIsArray($violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new FieldViolation($recordId, $field, $value, $message), $violation);
    }

    public function testValidateRecord(): void
    {
        $recordId = 123;
        $badField = 'foo';
        $badValue = 'bar';
        $goodField = 'foo';
        $goodValue = 'bar';
        $message = 'Oops!';

        $record = [$badField => $badValue, $goodField => $goodValue];

        $mockFields = [
            \Mockery::mock(BaseField::class, ['name' => $badField, 'unique' => false]),
            \Mockery::mock(BaseField::class, ['name' => $goodField, 'unique' => false]),
        ];

        $mockValidationError = \Mockery::mock(SchemaValidationError::class);
        $mockValidationError->extraDetails = [
            'field' => $badField,
            'value' => $badValue,
        ];
        $mockValidationError->shouldReceive('getMessage')
            ->andReturn($message);
        $this->stubSchemaValidationRecordError($record, $this->mockTableSchema, [$mockValidationError], $mockFields);

        $violations = $this->validator->validateRecord($recordId, $record);

        $this->assertIsArray($violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(new FieldViolation($recordId, $badField, $badValue, $message), $violation);
    }

    public function testValidateRecordWithMissingField(): void
    {
        $mockFields = [
            \Mockery::mock(BaseField::class, ['name' => 'col_a', 'unique' => false]),
            \Mockery::mock(BaseField::class, ['name' => 'col_b', 'unique' => false]),
        ];

        $record = ['col_a' => 123];

        $this->stubSchemaValidationRecordError($record, $this->mockTableSchema, [], $mockFields);

        $violations = $this->validator->validateRecord(1, $record);

        $this->assertIsArray($violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(RecordViolation::class, $violation);
        $this->assertEquals(new RecordViolation(1, 'Record is missing field/s: col_b'), $violation);
    }

    public function testValidateRecordWithExtraField(): void
    {
        $mockFields = [
            \Mockery::mock(BaseField::class, ['name' => 'col_a', 'unique' => false]),
        ];

        $record = ['col_a' => 123, 'col_b' => 234];

        $this->stubSchemaValidationRecordError($record, $this->mockTableSchema, [], $mockFields);

        $violations = $this->validator->validateRecord(1, $record);

        $this->assertIsArray($violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(RecordViolation::class, $violation);
        $this->assertEquals(new RecordViolation(1, 'Record has extra field/s: col_b'), $violation);
    }

    public function testValidateRecordWithUniqueField(): void
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

        $this->assertIsArray($violations);
        $this->assertCount(1, $violations);

        $violation = array_pop($violations);

        $this->assertInstanceOf(ViolationInterface::class, $violation);
        $this->assertEquals(
            new FieldViolation(2, $field, $record[$field], 'id: value is not unique.'),
            $violation
        );
    }

    public function testUnknownFieldException(): void
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
    ): void {
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
    ): void {
        $mockTableSchema->shouldReceive('validateRow')
            ->with($record)
            ->andReturn($validationErrors);
        $mockTableSchema->shouldReceive('fields')
            ->andReturn($fields);
    }
}
