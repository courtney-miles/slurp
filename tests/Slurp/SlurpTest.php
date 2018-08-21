<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:36 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Transform\Transformer;
use MilesAsylum\Slurp\Validate\Validator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SlurpTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ExtractorInterface|MockInterface
     */
    protected $mockExtractor;

    /**
     * @var LoaderInterface|MockInterface
     */
    protected $mockLoader;

    /**
     * @var Validator|MockInterface
     */
    protected $mockValidator;

    /**
     * @var Transformer|MockInterface
     */
    protected $mockTransformer;

    public function setUp()
    {
        parent::setUp();

        $this->mockExtractor = \Mockery::mock(ExtractorInterface::class);
        $this->mockLoader = \Mockery::mock(LoaderInterface::class);
        $this->mockValidator = \Mockery::mock(Validator::class);
        $this->mockTransformer = \Mockery::mock(Transformer::class);

        $this->mockValidator->shouldReceive('validateRow')
            ->andReturn([])
            ->byDefault();
        $this->mockTransformer->shouldReceive('transformRow')
            ->andReturnUsing(function ($row) {
                return $row;
            })->byDefault();

        $this->mockExtractor->shouldReceive('getColumns')
            ->andReturn([])
            ->byDefault();
    }

    public function testGetSource()
    {
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->assertSame($this->mockExtractor, $slurp->getExtractor());
    }

    public function testGetDest()
    {
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->assertSame($this->mockLoader, $slurp->getLoader());
    }

    public function testNumUpdateCalls()
    {
        $rows = [['foo', 'bar'], ['fee', 'baz']];
        $this->stubExtractorContent($this->mockExtractor, $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );

        foreach ($rows as $row) {
            $this->mockLoader->shouldReceive('loadRow')
                ->with($row)
                ->once();
        }

        $slurp->load();
    }

    public function testGrabRowReturnNullAfterLoad()
    {
        $this->stubExtractorContent($this->mockExtractor, [['foo', 'bar'], ['fee', 'baz']]);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('loadRow')->byDefault();

        $slurp->load();

        $this->assertNull($slurp->grabRow());
    }

    public function testGrabRowKeyReturnNullAfterLoad()
    {
        $this->stubExtractorContent($this->mockExtractor, [['foo', 'bar'], ['fee', 'baz']]);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('loadRow')->byDefault();
        $slurp->load();

        $this->assertNull($slurp->grabRowKey());
    }

    public function testApplyTransformationsToRows()
    {
        $rows = [['foo', 'bar']];

        $this->stubExtractorContent($this->mockExtractor, $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );

        $this->mockTransformer->shouldReceive('transformRow')
            ->with($rows[0])
            ->andReturn(['FOO', 'BAR']);

        $this->mockLoader->shouldReceive('loadRow')
            ->with(['FOO', 'BAR'])
            ->once();

        $slurp->load();
    }

    protected function createSlurp(
        ExtractorInterface $extractor,
        LoaderInterface $loader,
        Validator $validator,
        Transformer $transformer
    ) {
        return new Slurp($extractor, $loader, $validator, $transformer);
    }

    protected function stubExtractorContent(MockInterface $mockExtractor, array $rowValues)
    {
        $this->stubIteratorMethods($mockExtractor, $rowValues);
    }

    /**
     * @param MockInterface|\Iterator $mockIterator
     * @param array $items
     */
    protected function stubIteratorMethods(MockInterface $mockIterator, array $items)
    {
        $arrayIterator = new \ArrayIterator($items);

        $mockIterator->shouldReceive('rewind')
            ->andReturnUsing(function () use ($arrayIterator) {
                $arrayIterator->rewind();
            });
        $mockIterator->shouldReceive('current')
            ->andReturnUsing(function () use ($arrayIterator) {
                return $arrayIterator->current();
            });
        $mockIterator->shouldReceive('key')
            ->andReturnUsing(function () use ($arrayIterator) {
                return $arrayIterator->key();
            });
        $mockIterator->shouldReceive('next')
            ->andReturnUsing(function () use ($arrayIterator) {
                $arrayIterator->next();
            });
        $mockIterator->shouldReceive('valid')
            ->andReturnUsing(function () use ($arrayIterator) {
                return $arrayIterator->valid();
            });
    }
}
