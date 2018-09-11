<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 11:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\StageObserverInterface;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

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
        $this->mockValidator->shouldReceive('validateRecord')
            ->withAnyArgs()
            ->andReturn([])
            ->byDefault();

        $this->stage = new ValidationStage($this->mockValidator);
    }

    public function testValidateOnInvoke()
    {
        $recordId = 123;
        $record = ['bar'];
        $violations = ['__violation__'];

        $mockPayload = $this->createMockPayload($recordId, $record);

        $this->stubViolations($this->mockValidator, $recordId, $record, $violations);

        $mockPayload->shouldReceive('addViolations')
            ->with($violations)
            ->once();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }

    public function testNotifyObserverAfterValidate()
    {
        $mockObserver = \Mockery::mock(StageObserverInterface::class);
        $mockObserver->shouldReceive('update')
            ->with($this->stage)
            ->once();

        $this->stage->attachObserver($mockObserver);

        $mockPayload = $this->createMockPayload(213, []);

        ($this->stage)($mockPayload);
    }

    /**
     * @param int $recordId
     * @param array $record
     * @return SlurpPayload|MockInterface
     */
    public function createMockPayload(int $recordId, array $record)
    {
        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('getRowId')
            ->andReturn($recordId);
        $mockPayload->shouldReceive('getValues')
            ->andReturn($record);
        $mockPayload->shouldReceive('addViolations')
            ->withAnyArgs()
            ->byDefault();

        return $mockPayload;
    }

    public function stubViolations(MockInterface $mockValidator, int $recordId, array $record, array $violations)
    {
        $mockValidator->shouldReceive('validateRecord')
            ->with($recordId, $record)
            ->andReturn($violations);
    }
}
