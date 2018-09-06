<?php
/**
 * Author: Courtney Miles
 * Date: 5/09/18
 * Time: 8:52 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SchemaTransformer;

use frictionlessdata\tableschema\Fields\BaseField;
use frictionlessdata\tableschema\Fields\StringField;
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
