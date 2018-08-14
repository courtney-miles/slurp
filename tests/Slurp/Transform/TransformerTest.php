<?php
/**
 * Author: Courtney Miles
 * Date: 14/08/18
 * Time: 9:53 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform;

use MilesAsylum\Slurp\Transform\Transformation;
use MilesAsylum\Slurp\Transform\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Transform\TransformerLoader;
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
     * @var Transformer|MockInterface
     */
    protected $mockLoader;

    public function setUp()
    {
        $this->mockLoader = \Mockery::mock(TransformerLoader::class);
        $this->transformer = new Transformer($this->mockLoader, ['col_a', 'col_b']);
    }

    public function testTransformRow()
    {
        $row = ['foo', 'bar'];

        $mockTransformation = \Mockery::mock(Transformation::class);
        $mockTransformer = \Mockery::mock(TransformerInterface::class);

        $this->transformer->addColumnTransformations('col_a', $mockTransformation);

        $this->mockLoader->shouldReceive('loadTransformer')
            ->with($mockTransformation)
            ->andReturn($mockTransformer);

        $mockTransformer->shouldReceive('transform')
            ->with('foo', $mockTransformation)
            ->andReturn('FOO');

        $this->assertSame(
            ['FOO', 'bar'],
            $this->transformer->transformRow($row)
        );
    }
}
