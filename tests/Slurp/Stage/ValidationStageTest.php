<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 11:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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

        $this->mockValidator = \Mockery::mock(ValidatorInterface::class);

        $this->stage = new ValidationStage($this->mockValidator);
    }

    public function testValidateOnInvoke()
    {
        $recordId = 123;
        $record = ['bar'];
        $violations = ['__violation__'];

        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('getRowId')
            ->andReturn($recordId);
        $mockPayload->shouldReceive('getValues')
            ->andReturn($record);

        $this->mockValidator->shouldReceive('validateRecord')
            ->with($recordId, $record)
            ->andReturn($violations);

        $mockPayload->shouldReceive('addViolations')
            ->with($violations)
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }
}
