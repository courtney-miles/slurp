<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 10:50 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MilesAsylum\Slurp\Stage\TransformationStage
 */
class TransformationStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var TransformationStage
     */
    protected $stage;

    protected $valueName = 'foo';

    /**
     * @var Change|MockInterface
     */
    protected $mockChange;

    /**
     * @var TransformerInterface|MockInterface
     */
    protected $mockTransformer;

    public function setUp()
    {
        parent::setUp();

        $this->mockChange = \Mockery::mock(Change::class);
        $this->mockTransformer = \Mockery::mock(TransformerInterface::class);

        $this->stage = new TransformationStage(
            $this->valueName,
            $this->mockChange,
            $this->mockTransformer
        );
    }

    public function testReplaceWithTransformedValue()
    {
        $value = 'bar';
        $transValue = 'BAR';
        /** @var SlurpPayload $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();
        $mockPayload->setValue($this->valueName, $value);
        $mockPayload->shouldReceive('valueHasViolation')
            ->with($this->valueName)
            ->andReturn(false);

        $this->mockTransformer->shouldReceive('transform')
            ->with($value, $this->mockChange)
            ->andReturn($transValue);

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($transValue, $mockPayload->getValue($this->valueName));
    }

    public function testDoNotTransformWhereViolation()
    {
        $value = 123;
        /** @var SlurpPayload $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();
        $mockPayload->setValue($this->valueName, $value);
        $mockPayload->shouldReceive('valueHasViolation')
            ->with($this->valueName)
            ->andReturn(true);

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($value, $mockPayload->getValue($this->valueName));
    }

    public function testNoticeWherePayloadMissingValue()
    {
        $this->expectException(Notice::class);

        $mockPayload = \Mockery::mock(SlurpPayload::class);
        $mockPayload->shouldReceive('hasValue')
            ->with($this->valueName)
            ->andReturn(false);

        ($this->stage)($mockPayload);
    }

    public function testDoNotTransformOnMissingValue()
    {
        /** @var SlurpPayload $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();

        @($this->stage)($mockPayload);
        $this->assertNull($mockPayload->getValue($this->valueName));
    }
}
