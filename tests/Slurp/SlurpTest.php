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
        $this->stubExtractorContent($this->mockExtractor, [1, 2], $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('update')
            ->with($slurp)->times(count($rows));

        $slurp->load();
    }

    public function testGrabRowReturnNullAfterLoad()
    {
        $this->stubExtractorContent($this->mockExtractor, [1, 2], [['foo', 'bar'], ['fee', 'baz']]);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('update')->byDefault();

        $slurp->load();

        $this->assertNull($slurp->grabRow());
    }

    public function testGrabRowKeyReturnNullAfterLoad()
    {
        $this->stubExtractorContent($this->mockExtractor, [1, 2], [['foo', 'bar'], ['fee', 'baz']]);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('update')->byDefault();
        $slurp->load();

        $this->assertNull($slurp->grabRowKey());
    }

    public function testGetRowsOnUpdate()
    {
        $rows = [['foo', 'bar'], ['fee', 'baz']];
        $grabbedRows = [];

        $this->stubExtractorContent($this->mockExtractor, [1, 2], $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('update')
            ->withArgs(function (Slurp $slurp) use (&$grabbedRows) {
                $grabbedRows[] = $slurp->grabRow();

                return true;
            });

        $slurp->load();

        $this->assertSame($rows, $grabbedRows);
    }

    public function testGetRowKeysOnUpdate()
    {
        $rows = [['foo', 'bar'], 'a' => ['fee', 'baz']];
        $grabbedKeys = [];

        $this->stubExtractorContent($this->mockExtractor, [1, 2], $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );
        $this->mockLoader->shouldReceive('update')
            ->withArgs(function (Slurp $slurp) use (&$grabbedKeys) {
                $grabbedKeys[] = $slurp->grabRowKey();

                return true;
            });

        $slurp->load();

        $this->assertSame(array_keys($rows), $grabbedKeys);
    }

    public function testApplyTransformationsToRows()
    {
        $rows = [['foo', 'bar']];
        $grabbedRows = [];

        $this->stubExtractorContent($this->mockExtractor, ['eyh', 'bhe'], $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );

        $this->mockTransformer->shouldReceive('transformRow')
            ->with($rows[0])
            ->andReturn(['FOO', 'BAR']);

        $this->mockLoader->shouldReceive('update')
            ->withArgs(function (Slurp $etlClient) use (&$grabbedRows) {
                $grabbedRows[] = $etlClient->grabRow();

                return true;
            });

        $slurp->load();

        $this->assertSame([['FOO', 'BAR']], $grabbedRows);
    }

    public function testValidateRows()
    {
        $rows = [['foo', 'bar']];
        $mockViolationList = \Mockery::mock(ConstraintViolationListInterface::class);
        $mockViolationList->shouldReceive('count')->andReturn(0);
        /** @var ConstraintViolationListInterface[] $grabbedViolationLists */
        $grabbedViolationLists = [];

        $this->stubExtractorContent($this->mockExtractor, ['col_one', 'col_two'], $rows);
        $slurp = $this->createSlurp(
            $this->mockExtractor,
            $this->mockLoader,
            $this->mockValidator,
            $this->mockTransformer
        );

        $this->mockValidator->shouldReceive('validateRow')
            ->with($rows[0], 0)
            ->andReturn($mockViolationList)
            ->once();

        $this->mockLoader->shouldReceive('update')
            ->withArgs(function (Slurp $slurp) use (&$grabbedViolationLists) {
                $grabbedViolationLists[] = $slurp->grabRowViolations();

                return true;
            });

        $slurp->load();

        $this->assertCount(1, $grabbedViolationLists);
        $this->assertSame($mockViolationList, $grabbedViolationLists[0]);
    }

    protected function createSlurp(
        ExtractorInterface $extractor,
        LoaderInterface $loader,
        Validator $validator,
        Transformer $transformer
    ) {
        return new Slurp($extractor, $loader, $validator, $transformer);
    }

    protected function stubExtractorContent(MockInterface $mockExtractor, array $columns, array $rowValues)
    {
        $mockExtractor->shouldReceive('getColumns')
            ->andReturn($columns);
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
