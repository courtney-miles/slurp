<?php
/**
 * Author: Courtney Miles
 * Date: 5/09/18
 * Time: 8:52 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SchemaTransformer;

use Carbon\Carbon;
use frictionlessdata\tableschema\Fields\DateField;
use frictionlessdata\tableschema\Fields\DatetimeField;
use frictionlessdata\tableschema\Fields\TimeField;
use frictionlessdata\tableschema\Schema;
use MilesAsylum\Slurp\Transform\Exception\TransformationException;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class SchemaTransformerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Schema|MockInterface
     */
    protected $mockSchema;

    /**
     * @var SchemaTransformer
     */
    protected $schemaTransformer;

    public function setUp()
    {
        $this->mockSchema = \Mockery::mock(Schema::class);
        $this->schemaTransformer = new SchemaTransformer($this->mockSchema);
    }

    public function testTransformField()
    {
        $this->markTestIncomplete(
            'Unable to test transforming a field because BaseField::castValue() is marked as final.'
        );
    }

    public function testTransformRecord()
    {
        $record = ['foo' => 123];
        $castRecord = ['foo' => 321];

        $this->mockSchema->shouldReceive('castRow')
            ->with($record)
            ->andReturn($castRecord);

        $this->assertSame($castRecord, $this->schemaTransformer->transformRecord($record));
    }

    /**
     * @dataProvider getComplexTypeConversionTestData
     * @param string $fieldClass
     * @param mixed $complexValue
     * @param mixed $scalarValue
     * @throws TransformationException
     */
    public function testConvertComplexTypeBackToScalar(string $fieldClass, $complexValue, $scalarValue)
    {
        $fieldName = 'foo';

        $this->mockSchema->shouldReceive('castRow')
            ->withAnyArgs()
            ->andReturn([$fieldName => $complexValue]);
        $this->mockSchema->shouldReceive('field')
            ->with($fieldName)
            ->andReturn(\Mockery::mock($fieldClass));

        $this->assertSame(
            [$fieldName => $scalarValue],
            $this->schemaTransformer->transformRecord(['anything'])
        );
    }

    public function getComplexTypeConversionTestData()
    {
        return [
            [TimeField::class, [12, 23, 34], '12:23:34'],
            [TimeField::class, null, null],
            [DateField::class, new Carbon('2018-02-01'), '2018-02-01'],
            [DateField::class, null, null],
            [DatetimeField::class, new Carbon('2018-02-01 12:23:34'), '2018-02-01 12:23:34'],
            [DatetimeField::class, null, null],
        ];
    }

    public function testExceptionFromCastWhenTransformingRecord()
    {
        $msg = 'fail';
        $this->expectException(TransformationException::class);
        $this->expectExceptionMessage('An error occurred transforming a record: ' . $msg);

        $record = ['foo' => 123];
        $this->mockSchema->shouldReceive('castRow')
            ->with($record)
            ->andThrow(\Exception::class, $msg);

        $this->schemaTransformer->transformRecord($record);
    }
}
