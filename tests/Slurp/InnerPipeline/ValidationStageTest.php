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

namespace MilesAsylum\Slurp\Tests\Slurp\InnerPipeline;

use MilesAsylum\Slurp\Event\RecordValidatedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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

        $this->mockValidator = Mockery::mock(ValidatorInterface::class);
        $this->mockValidator->shouldReceive('validateRecord')
            ->withAnyArgs()
            ->andReturn([])
            ->byDefault();

        $this->stage = new ValidationStage($this->mockValidator);
    }

    public function testValidateOnInvoke(): void
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

    public function testDoNotValidateFiltered(): void
    {
        $mockPayload = $this->createMockPayload(123, ['bar']);
        $mockPayload->shouldReceive('isFiltered')
            ->andReturn(true);

        $this->mockValidator->shouldReceive('validateRecord')
            ->never();
        $mockPayload->shouldReceive('addViolations')
            ->never();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
    }

    public function testDispatchEventOnValidatedRecord(): void
    {
        $mockPayload = $this->createMockPayload(213, []);
        $mockDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(RecordValidatedEvent::NAME, Mockery::type(RecordValidatedEvent::class))
            ->once();

        $this->stage->setEventDispatcher($mockDispatcher);
        ($this->stage)($mockPayload);
    }

    public function testDoNotDispatchEventWhenFilteredAndNotValidated(): void
    {
        $mockPayload = $this->createMockPayload(213, [], true);
        $mockDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->never();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($mockPayload);
    }

    /**
     * @param int $recordId
     * @param array $record
     * @param bool $isFiltered
     * @return SlurpPayload|MockInterface
     */
    public function createMockPayload(int $recordId, array $record, bool $isFiltered = false)
    {
        $mockPayload = Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('getRecordId')
            ->andReturn($recordId);
        $mockPayload->shouldReceive('getRecord')
            ->andReturn($record);
        $mockPayload->shouldReceive('isFiltered')
            ->andReturn($isFiltered)
            ->byDefault();
        $mockPayload->shouldReceive('addViolations')
            ->withAnyArgs()
            ->byDefault();

        return $mockPayload;
    }

    public function stubViolations(MockInterface $mockValidator, int $recordId, array $record, array $violations): void
    {
        $mockValidator->shouldReceive('validateRecord')
            ->with($recordId, $record)
            ->andReturn($violations);
    }
}
