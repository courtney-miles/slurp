<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 11:09 AM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp;

use InvalidArgumentException;
use MilesAsylum\Slurp\PHPUnit\StubValidatorTrait;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SlurpPayloadTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use StubValidatorTrait;

    public function testHasNotValue(): void
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasField('foo'));
        $this->assertNull($payload->getFieldValue('foo'));
    }

    public function testSetValue(): void
    {
        $payload = new SlurpPayload();
        $payload->setFieldValue('foo', 123);

        $this->assertTrue($payload->hasField('foo'));
        $this->assertSame(123, $payload->getFieldValue('foo'));
    }

    public function testSetValues(): void
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

    public function testReplaceValue(): void
    {
        $payload = new SlurpPayload();
        $payload->setFieldValue('foo', 123);

        $payload->replaceFieldValue('foo', 234);

        $this->assertSame(234, $payload->getFieldValue('foo'));
    }

    public function testExceptionOnReplaceUnknownValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payload = new SlurpPayload();
        $payload->replaceFieldValue('foo', 234);
    }

    public function testGetRowIdDefault(): void
    {
        $payload = new SlurpPayload();

        $this->assertNull($payload->getRecordId());
    }

    public function testSetId(): void
    {
        $payload = new SlurpPayload();
        $payload->setRecordId(123);

        $this->assertSame(123, $payload->getRecordId());
    }

    public function testHasNoViolations(): void
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasViolations());
        $this->assertfalse($payload->fieldHasViolation('bar'));
        $this->assertSame([], $payload->getViolations());
    }

    public function testHasViolationOfType(): void
    {
        $payload = new SlurpPayload();

        $mockViolation = Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->hasViolations(FieldViolation::class));
    }

    public function testDoesNotHaveViolationOfType(): void
    {
        $payload = new SlurpPayload();

        $mockViolation = Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertFalse($payload->hasViolations(RecordViolation::class));
    }

    public function testAddFirstViolations(): void
    {
        $payload = new SlurpPayload();
        $mockViolation = Mockery::mock(FieldViolation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->hasViolations());
        $this->assertSame([$mockViolation], $payload->getViolations());
    }

    public function testValueHasViolation(): void
    {
        $payload = new SlurpPayload();
        $mockViolation = Mockery::mock(FieldViolation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->fieldHasViolation('foo'));
    }

    public function testValueHasNotViolation(): void
    {
        $payload = new SlurpPayload();
        $mockViolation = Mockery::mock(FieldViolation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertfalse($payload->fieldHasViolation('bar'));
    }

    public function testSetLoadAborted(): void
    {
        $payload = new SlurpPayload();
        $this->assertFalse($payload->isLoadAborted());

        $payload->setLoadAborted(true);
        $this->assertTrue($payload->isLoadAborted());
    }

    public function testSetFiltered(): void
    {
        $payload = new SlurpPayload();
        $this->assertFalse($payload->isFiltered());

        $payload->setFiltered(true);
        $this->assertTrue($payload->isFiltered());
    }
}
