<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 11:09 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use MilesAsylum\Slurp\PHPUnit\StubValidatorTrait;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SlurpPayloadTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use StubValidatorTrait;

    public function testHasNotValue()
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasField('foo'));
        $this->assertNull($payload->getFieldValue('foo'));
    }

    public function testSetValue()
    {
        $payload = new SlurpPayload();
        $payload->setFieldValue('foo', 123);

        $this->assertTrue($payload->hasField('foo'));
        $this->assertSame(123, $payload->getFieldValue('foo'));
    }

    public function testSetValues()
    {
        $values = ['foo' => 123, 'bar' => 234];
        $payload = new SlurpPayload();
        $payload->setRecord($values);

        $this->assertSame($values, $payload->getRecord());

        foreach ($values as $name => $value) {
            $this->assertTrue($payload->hasField($name));
            $this->assertSame($value, $payload->getFieldValue($name));
        }
    }

    public function testReplaceValue()
    {
        $payload = new SlurpPayload();
        $payload->setFieldValue('foo', 123);

        $payload->replaceFieldValue('foo', 234);

        $this->assertSame(234, $payload->getFieldValue('foo'));
    }

    public function testExceptionOnReplaceUnknownValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $payload = new SlurpPayload();
        $payload->replaceFieldValue('foo', 234);
    }

    public function testGetRowIdDefault()
    {
        $payload = new SlurpPayload();

        $this->assertNull($payload->getRecordId());
    }

    public function testSetId()
    {
        $payload = new SlurpPayload();
        $payload->setRecordId(123);

        $this->assertSame(123, $payload->getRecordId());
    }

    public function testHasNoViolations()
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasViolations());
        $this->assertfalse($payload->fieldHasViolation('bar'));
        $this->assertSame([], $payload->getViolations());
    }

    public function testHasViolationOfType()
    {
        $payload = new SlurpPayload();

        $mockViolation = \Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->hasViolations(FieldViolation::class));
    }

    public function testDoesNotHaveViolationOfType()
    {
        $payload = new SlurpPayload();

        $mockViolation = \Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertFalse($payload->hasViolations(RecordViolation::class));
    }

    public function testAddFirstViolations()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->hasViolations());
        $this->assertSame([$mockViolation], $payload->getViolations());
    }

    public function testValueHasViolation()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(FieldViolation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->fieldHasViolation('foo'));
    }

    public function testValueHasNotViolation()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(FieldViolation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertfalse($payload->fieldHasViolation('bar'));
    }

    public function testSetLoadAborted()
    {
        $payload = new SlurpPayload();
        $this->assertFalse($payload->isLoadAborted());

        $payload->setLoadAborted(true);
        $this->assertTrue($payload->isLoadAborted());
    }
}
