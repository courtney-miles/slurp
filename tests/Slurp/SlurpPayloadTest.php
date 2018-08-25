<?php
/**
 * Author: Courtney Miles
 * Date: 25/08/18
 * Time: 11:09 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use MilesAsylum\Slurp\SlurpPayload;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SlurpPayloadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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
        $this->assertNull($payload->getViolations());
    }

    public function testAddFirstViolations()
    {
        $payload = new SlurpPayload();
        $mockViolations = \Mockery::mock(ConstraintViolationListInterface::class);
        $this->stubViolationList($mockViolations, ['__violation__']);

        $payload->addViolations($mockViolations);

        $this->assertTrue($payload->hasViolations());
        $this->assertSame($mockViolations, $payload->getViolations());
    }

    public function testAddMoreViolations()
    {
        $payload = new SlurpPayload();
        $mockFirstViolations = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockMoreViolations = \Mockery::mock(ConstraintViolationListInterface::class);

        $this->stubViolationList($mockFirstViolations, ['__violation__']);
        $mockFirstViolations->shouldReceive('addAll')
            ->with($mockMoreViolations)
            ->once();

        $payload->addViolations($mockFirstViolations);
        $payload->addViolations($mockMoreViolations);

        $this->assertTrue($payload->hasViolations());
        $this->assertSame($mockFirstViolations, $payload->getViolations());
    }

    public function testValueHasViolation()
    {
        $payload = new SlurpPayload();
        $mockViolations = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = \Mockery::mock(ConstraintViolationInterface::class);
        $this->stubViolationList($mockViolations, [$mockViolation]);

        $mockViolation->shouldReceive('getPropertyPath')
            ->andReturn('foo');

        $payload->addViolations($mockViolations);

        $this->assertTrue($payload->valueHasViolation('foo'));
    }

    public function testValueHasNotViolation()
    {
        $payload = new SlurpPayload();
        $mockViolations = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolation = \Mockery::mock(ConstraintViolationInterface::class);
        $this->stubViolationList($mockViolations, [$mockViolation]);

        $mockViolation->shouldReceive('getPropertyPath')
            ->andReturn('foo');

        $payload->addViolations($mockViolations);

        $this->assertfalse($payload->valueHasViolation('bar'));
    }

    protected function stubViolationList(MockInterface $mockViolationList, array $violations)
    {
        $arrayIterator = new \ArrayIterator($violations);

        $mockViolationList->shouldReceive('count')
            ->andReturn(count($violations));

        $mockViolationList->shouldReceive('getIterator')
            ->andReturn($arrayIterator);
    }
}
