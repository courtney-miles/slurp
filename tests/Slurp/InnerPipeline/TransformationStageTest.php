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

use MilesAsylum\Slurp\Event\RecordTransformedEvent;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \MilesAsylum\Slurp\InnerPipeline\TransformationStage
 */
class TransformationStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var TransformationStage
     */
    protected $stage;

    /**
     * @var TransformerInterface|MockInterface
     */
    protected $mockTransformer;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockTransformer = \Mockery::mock(TransformerInterface::class);
        $this->mockTransformer->shouldReceive('transformRecord')
            ->byDefault();

        $this->stage = new TransformationStage(
            $this->mockTransformer
        );
    }

    public function testReplaceWithTransformedValue(): void
    {
        $field = 'foo';
        $value = 123;
        $transValue = 321;
        $mockPayload = $this->createMockPayload($field, $value, false);

        $this->mockTransformer->shouldReceive('transformRecord')
            ->with($mockPayload->getRecord())
            ->andReturn([$field => $transValue]);

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($transValue, $mockPayload->getFieldValue($field));
    }

    public function testDoNotTransformWhereViolation(): void
    {
        $field = 'foo';
        $value = 123;
        $mockPayload = $this->createMockPayload($field, $value, true);

        $this->mockTransformer->shouldReceive('transformRecord')
            ->with([$field => $value])
            ->never();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($value, $mockPayload->getFieldValue($field));
    }

    public function testDispatchEventOnTransformRecord(): void
    {
        $mockPayload = $this->createMockPayload('foo', 123, false);
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')
            ->with(\Mockery::type(RecordTransformedEvent::class), RecordTransformedEvent::NAME)
            ->once();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($mockPayload);
    }

    public function testDoNotDispatchEventOnNotTransformRecord(): void
    {
        $mockPayload = $this->createMockPayload('foo', 123, true);
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->never();

        $this->stage->setEventDispatcher($mockDispatcher);

        ($this->stage)($mockPayload);
    }

    /**
     * @return MockInterface|SlurpPayload
     */
    protected function createMockPayload(string $field, $value, bool $hasViolation): MockInterface
    {
        /** @var SlurpPayload|MockInterface $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();
        $mockPayload->setFieldValue($field, $value);
        $mockPayload->shouldReceive('hasViolations')
            ->andReturn($hasViolation);

        return $mockPayload;
    }
}
