<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 11:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\ValidationStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ValidationStage
     */
    protected $stage;

    protected $valueName = 'foo';

    /**
     * @var Constraint|MockInterface
     */
    protected $mockConstraint;

    /**
     * @var ValidatorInterface|MockInterface
     */
    protected $mockValidator;

    public function setUp()
    {
        parent::setUp();

        $this->mockConstraint = \Mockery::mock(Constraint::class);
        $this->mockValidator = \Mockery::mock(ValidatorInterface::class);

        $this->stage = new ValidationStage($this->valueName, $this->mockConstraint, $this->mockValidator);
    }

    public function testValidateOnInvoke()
    {
        $value = 123;

        $mockViolations = \Mockery::mock(ConstraintViolationListInterface::class);

        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('hasValue')
            ->with($this->valueName)
            ->andReturn(true);
        $mockPayload->shouldReceive('getValue')
            ->with($this->valueName)
            ->andReturn($value);

        $this->mockValidator->shouldReceive('validate')
            ->with($value, $this->mockConstraint)
            ->andReturn($mockViolations);

        $mockPayload->shouldReceive('addViolations')
            ->with($mockViolations)
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }

    public function testNoticeOnMissingValue()
    {
        $this->expectException(Notice::class);

        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('hasValue')
            ->with($this->valueName)
            ->andReturn(false);

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }
}
