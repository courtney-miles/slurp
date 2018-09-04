<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 11:09 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use MilesAsylum\Slurp\PHPUnit\StubValidatorTrait;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Validate\Violation;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SlurpPayloadTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use StubValidatorTrait;

    public function testHasNotValue()
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasValue('foo'));
        $this->assertNull($payload->getValue('foo'));
    }

    public function testSetValue()
    {
        $payload = new SlurpPayload();
        $payload->setValue('foo', 123);

        $this->assertTrue($payload->hasValue('foo'));
        $this->assertSame(123, $payload->getValue('foo'));
    }

    public function testSetValues()
    {
        $values = ['foo' => 123, 'bar' => 234];
        $payload = new SlurpPayload();
        $payload->setValues($values);

        $this->assertSame($values, $payload->getValues());

        foreach ($values as $name => $value) {
            $this->assertTrue($payload->hasValue($name));
            $this->assertSame($value, $payload->getValue($name));
        }
    }

    public function testReplaceValue()
    {
        $payload = new SlurpPayload();
        $payload->setValue('foo', 123);

        $payload->replaceValue('foo', 234);

        $this->assertSame(234, $payload->getValue('foo'));
    }

    public function testExceptionOnReplaceUnknownValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $payload = new SlurpPayload();
        $payload->replaceValue('foo', 234);
    }

    public function testGetRowIdDefault()
    {
        $payload = new SlurpPayload();

        $this->assertNull($payload->getRowId());
    }

    public function testSetId()
    {
        $payload = new SlurpPayload();
        $payload->setId(123);

        $this->assertSame(123, $payload->getRowId());
    }

    public function testHasNoViolations()
    {
        $payload = new SlurpPayload();

        $this->assertFalse($payload->hasViolations());
        $this->assertfalse($payload->valueHasViolation('bar'));
        $this->assertSame([], $payload->getViolations());
    }

    public function testAddFirstViolations()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(Violation::class);
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->hasViolations());
        $this->assertSame([$mockViolation], $payload->getViolations());
    }

    public function testValueHasViolation()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(Violation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertTrue($payload->valueHasViolation('foo'));
    }

    public function testValueHasNotViolation()
    {
        $payload = new SlurpPayload();
        $mockViolation = \Mockery::mock(Violation::class);
        $mockViolation->shouldReceive('getField')
            ->andReturn('foo');
        $payload->addViolations([$mockViolation]);

        $this->assertfalse($payload->valueHasViolation('bar'));
    }
}
