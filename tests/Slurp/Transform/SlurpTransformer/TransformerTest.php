<?php
/**
 * Author: Courtney Miles
 * Date: 5/09/18
 * Time: 10:00 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\ChangeTransformerInterface;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TransformerLoader;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Transformer
     */
    protected $transformer;

    /**
     * @var TransformerLoader|MockInterface
     */
    protected $mockLoader;

    public function setUp()
    {
        $this->mockLoader = \Mockery::mock(TransformerLoader::class);

        $this->transformer = new Transformer($this->mockLoader);
    }

    public function testTransformField()
    {
        $field = 'foo';
        $value = 123;
        $newValue = 321;

        $mockChange = \Mockery::mock(Change::class);
        $mockChangeTransformer = \Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChange)
            ->andReturn($newValue);
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChange);

        $this->assertSame($newValue, $this->transformer->transformField($field, $value));
    }

    public function testTransformRecord()
    {
        $field = 'foo';
        $value = 123;
        $newValue = 321;

        $mockChange = \Mockery::mock(Change::class);
        $mockChangeTransformer = \Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChange)
            ->andReturn($newValue);
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChange);

        $this->assertSame([$field => $newValue], $this->transformer->transformRecord([$field => $value]));
    }

    protected function stubTransformerLoader(
        MockInterface $mockLoader,
        Change $change,
        ChangeTransformerInterface $changeTransformer
    ) {
        $mockLoader->shouldReceive('loadTransformer')
            ->with($change)
            ->andReturn($changeTransformer);
    }
}
