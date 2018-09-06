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

    /**
     * @var TransformerInterface|MockInterface
     */
    protected $mockTransformer;

    public function setUp()
    {
        parent::setUp();

        $this->mockTransformer = \Mockery::mock(TransformerInterface::class);

        $this->stage = new TransformationStage(
            $this->mockTransformer
        );
    }

    public function testReplaceWithTransformedValue()
    {
        $field = 'foo';
        $value = 123;
        $transValue = 321;

        /** @var SlurpPayload $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();
        $mockPayload->setValue($field, $value);
        $mockPayload->shouldReceive('hasViolations')
            ->andReturn(false);

        $this->mockTransformer->shouldReceive('transformRecord')
            ->with($mockPayload->getValues())
            ->andReturn([$field => $transValue]);

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($transValue, $mockPayload->getValue($field));
    }

    public function testDoNotTransformWhereViolation()
    {
        $field = 'foo';
        $value = 123;

        /** @var SlurpPayload $mockPayload */
        $mockPayload = \Mockery::mock(SlurpPayload::class)->makePartial();
        $mockPayload->setValue($field, $value);
        $mockPayload->shouldReceive('hasViolations')
            ->andReturn(true);

        $this->mockTransformer->shouldReceive('transformRecord')
            ->with([$field => $value])
            ->never();

        $this->assertSame($mockPayload, ($this->stage)($mockPayload));
        $this->assertSame($value, $mockPayload->getValue($field));
    }
}
