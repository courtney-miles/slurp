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

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\ChangeTransformerInterface;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TransformerLoader;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Error\Warning;
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

    public function setUp(): void
    {
        $this->mockLoader = Mockery::mock(TransformerLoader::class);

        $this->transformer = new Transformer($this->mockLoader);
    }

    public function testTransformField(): void
    {
        $field = 'foo';
        $value = 123;
        $newValue = 321;

        $mockChange = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChange)
            ->andReturn($newValue);
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChange);

        $this->assertSame($newValue, $this->transformer->transformField($field, $value));
    }

    public function testTransformRecord(): void
    {
        $field = 'foo';
        $value = 123;
        $newValue = 321;

        $mockChange = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChange)
            ->andReturn($newValue);
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChange);

        $this->assertSame([$field => $newValue], $this->transformer->transformRecord([$field => $value]));
    }

    public function testTransformNoChanges(): void
    {
        $field = 'foo';
        $value = 123;

        $this->assertSame([$field => $value], $this->transformer->transformRecord([$field => $value]));
    }

    public function testTransformUndefinedField(): void
    {
        $record = ['foo' => 123];

        $mockChange = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->never();
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);
        $this->transformer->setFieldChanges('bar', $mockChange);

        $this->assertSame(
            $record,
            @$this->transformer->transformRecord($record)
        );
    }

    public function testTransformUndefinedFieldTriggersWarning(): void
    {
        $this->expectException(Warning::class);
        $this->expectExceptionMessage(
            'Unable to apply transformation for field \'bar\'. The supplied record did not contain this field.'
        )
        ;

        $mockChange = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->never();
        $this->stubTransformerLoader($this->mockLoader, $mockChange, $mockChangeTransformer);
        $this->transformer->setFieldChanges('bar', $mockChange);

        $this->transformer->transformRecord(['foo' => 123]);
    }

    public function testAddChange(): void
    {
        $field = 'foo';
        $value = 123;
        $newValueOne = 321;
        $newValueTwo = 654;
        $mockChangeOne = Mockery::mock(Change::class);
        $mockChangeTwo = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChangeOne)
            ->andReturn($newValueOne);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($newValueOne, $mockChangeTwo)
            ->andReturn($newValueTwo);
        $this->stubTransformerLoader($this->mockLoader, $mockChangeOne, $mockChangeTransformer);
        $this->stubTransformerLoader($this->mockLoader, $mockChangeTwo, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChangeOne);
        $this->transformer->addFieldChange($field, $mockChangeTwo);

        $this->assertSame([$field => $newValueTwo], $this->transformer->transformRecord([$field => $value]));
    }

    public function testResetChanges(): void
    {
        $field = 'foo';
        $value = 123;
        $newValueTwo = 654;
        $mockChangeOne = Mockery::mock(Change::class);
        $mockChangeTwo = Mockery::mock(Change::class);
        $mockChangeTransformer = Mockery::mock(ChangeTransformerInterface::class);
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChangeOne)
            ->never();
        $mockChangeTransformer->shouldReceive('transform')
            ->with($value, $mockChangeTwo)
            ->andReturn($newValueTwo);
        $this->stubTransformerLoader($this->mockLoader, $mockChangeTwo, $mockChangeTransformer);

        $this->transformer->setFieldChanges($field, $mockChangeOne);
        // Replace the previously set change.
        $this->transformer->setFieldChanges($field, $mockChangeTwo);

        $this->assertSame([$field => $newValueTwo], $this->transformer->transformRecord([$field => $value]));
    }

    protected function stubTransformerLoader(
        MockInterface $mockLoader,
        Change $change,
        ChangeTransformerInterface $changeTransformer
    ): void {
        $mockLoader->shouldReceive('loadTransformer')
            ->with($change)
            ->andReturn($changeTransformer);
    }
}
