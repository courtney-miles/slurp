<?php
/**
 * Author: Courtney Miles
 * Date: 24/09/18
 * Time: 5:42 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerStage\FiltrationStage;
use MilesAsylum\Slurp\InnerStage\InnerProcessor;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitDmlInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterStage\OuterProcessor;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpBuilder;
use MilesAsylum\Slurp\SlurpFactory;
use MilesAsylum\Slurp\OuterStage\FinaliseStage;
use MilesAsylum\Slurp\OuterStage\InvokePipelineStage;
use MilesAsylum\Slurp\InnerStage\LoadStage;
use MilesAsylum\Slurp\InnerStage\StageObserverInterface;
use MilesAsylum\Slurp\InnerStage\TransformationStage;
use MilesAsylum\Slurp\InnerStage\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class SlurpBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SlurpBuilder
     */
    protected $builder;

    /**
     * @var PipelineBuilder|MockInterface
     */
    protected $mockInnerPipelineBuilder;

    /**
     * @var PipelineBuilder|MockInterface
     */
    protected $mockOuterPipelineBuilder;

    /**
     * @var SlurpFactory|MockInterface
     */
    protected $mockFactory;

    /**
     * @var ConstraintValidator|MockInterface
     */
    protected $mockConstraintValidator;

    /**
     * @var Transformer|MockInterface
     */
    protected $mockTransformer;

    /**
     * @var ConstraintFilter|MockInterface
     */
    protected $mockConstraintFilter;

    /**
     * @var Slurp|MockInterface
     */
    protected $mockSlurp;

    /**
     * @var PipelineInterface|MockInterface
     */
    protected $mockInnerPipeline;

    /**
     * @var PipelineInterface|MockInterface
     */
    protected $mockOuterPipeline;

    /**
     * @var InvokePipelineStage|MockInterface
     */
    protected $mockInvokeExtractionPipeline;

    /**
     * @var OuterProcessor|MockInterface
     */
    protected $mockOuterProcessor;

    /**
     * @var InnerProcessor|MockInterface
     */
    protected $mockInnerProcessor;

    public function setUp()
    {
        parent::setUp();

        $this->mockInnerPipelineBuilder = \Mockery::mock(PipelineBuilder::class);
        $this->mockOuterPipelineBuilder = \Mockery::mock(PipelineBuilder::class);
        $this->mockFactory = \Mockery::mock(SlurpFactory::class);
        $this->mockSlurp = \Mockery::mock(Slurp::class);
        $this->mockConstraintValidator = \Mockery::mock(ConstraintValidator::class);
        $this->mockTransformer = \Mockery::mock(Transformer::class);
        $this->mockConstraintFilter = \Mockery::mock(ConstraintFilter::class);

        $this->mockInnerPipeline = \Mockery::mock(PipelineInterface::class);
        $this->mockOuterPipeline = \Mockery::mock(PipelineInterface::class);
        $this->mockInvokeExtractionPipeline = \Mockery::mock(InvokePipelineStage::class);
        $this->mockOuterProcessor = \Mockery::mock(OuterProcessor::class);
        $this->mockInnerProcessor = \Mockery::mock(InnerProcessor::class);

        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->byDefault();
        $this->mockInnerPipelineBuilder->shouldReceive('build')
            ->andReturn($this->mockInnerPipeline)
            ->byDefault();

        $this->mockFactory->shouldReceive('createEtlInvokePipelineStage')
            ->with($this->mockInnerPipeline)
            ->andReturn($this->mockInvokeExtractionPipeline)
            ->byDefault();

        $this->mockOuterPipelineBuilder->shouldReceive('add')
            ->byDefault();
        $this->mockOuterPipelineBuilder->shouldReceive('build')
            ->andReturn($this->mockOuterPipeline)
            ->byDefault();

        $this->mockFactory->shouldReceive('createSlurp')
            ->with($this->mockOuterPipeline)
            ->andReturn($this->mockSlurp)
            ->byDefault();

        $this->mockFactory->shouldReceive('createConstraintValidator')
            ->andReturn($this->mockConstraintValidator)
            ->byDefault();
        $this->mockFactory->shouldReceive('createTransformer')
            ->andReturn($this->mockTransformer)
            ->byDefault();
        $this->mockFactory->shouldReceive('createConstraintFilter')
            ->andReturn($this->mockConstraintFilter)
            ->byDefault();
        $this->mockFactory->shouldReceive('createOuterProcessor')
            ->andReturn($this->mockOuterProcessor)
            ->byDefault();
        $this->mockFactory->shouldReceive('createInnerProcessor')
            ->andReturn($this->mockInnerProcessor)
            ->byDefault();

        $this->builder = new SlurpBuilder(
            $this->mockInnerPipelineBuilder,
            $this->mockOuterPipelineBuilder,
            $this->mockFactory
        );
    }

    public function testCreateBuilder()
    {
        $this->assertInstanceOf(
            SlurpBuilder::class,
            SlurpBuilder::create()
        );
    }

    public function testBuild()
    {
        $this->assertInstanceOf(
            Slurp::class,
            $this->builder->build()
        );
    }

    public function testSetTableSchema()
    {
        $mockTableSchema = \Mockery::mock(Schema::class);
        $mockValidationStage = \Mockery::mock(ValidationStage::class);
        $mockTransformationStage = \Mockery::mock(TransformationStage::class);
        $mockSchemaValidator = \Mockery::mock(SchemaValidator::class);
        $mockSchemaTransformer = \Mockery::mock(SchemaTransformer::class);

        $this->mockFactory->shouldReceive('createSchemaValidator')
            ->with($mockTableSchema)
            ->andReturn($mockSchemaValidator);
        $this->mockFactory->shouldReceive('createValidationStage')
            ->with($mockSchemaValidator)
            ->andReturn($mockValidationStage);
        $this->mockFactory->shouldReceive('createSchemaTransformer')
            ->with($mockTableSchema)
            ->andReturn($mockSchemaTransformer);
        $this->mockFactory->shouldReceive('createTransformationStage')
            ->with($mockSchemaTransformer)
            ->andReturn($mockTransformationStage);

        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockValidationStage)
            ->once();
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockTransformationStage)
            ->once();

        $this->builder->setTableSchema($mockTableSchema);
        $this->builder->build();
    }

    public function testCreateTableSchemaFromPath()
    {
        $path = '/foo/bar.json';
        $mockTableSchema = \Mockery::mock(Schema::class);

        $this->mockFactory->shouldReceive('createTableSchemaFromPath')
            ->with($path)
            ->andReturn($mockTableSchema);

        $this->assertSame($mockTableSchema, $this->builder->createTableSchemaFromPath($path));
    }

    public function testCreateTableSchemaFromArray()
    {
        $array = ['foo'];
        $mockTableSchema = \Mockery::mock(Schema::class);

        $this->mockFactory->shouldReceive('createTableSchemaFromArray')
            ->with($array)
            ->andReturn($mockTableSchema);

        $this->assertSame($mockTableSchema, $this->builder->createTableSchemaFromArray($array));
    }

    public function testAddValidationConstraint()
    {
        $mockConstraint = \Mockery::mock(Constraint::class);
        $mockValidationStage = \Mockery::mock(ValidationStage::class);

        $this->mockConstraintValidator->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraint)
            ->once();
        $this->mockFactory->shouldReceive('createValidationStage')
            ->with($this->mockConstraintValidator)
            ->andReturn($mockValidationStage);
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockValidationStage)
            ->once();

        $this->builder->addValidationConstraint(
            'foo',
            $mockConstraint
        );
        $this->builder->build();
    }

    public function testAddMultipleValidationConstraint()
    {
        $mockConstraintOne = \Mockery::mock(Constraint::class);
        $mockConstraintTwo = \Mockery::mock(Constraint::class);
        $mockValidationStage = \Mockery::mock(ValidationStage::class);

        $this->mockFactory->shouldReceive('createConstraintValidator')
            ->andReturn($this->mockConstraintValidator)
            ->once();
        $this->mockConstraintValidator->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraintOne);
        $this->mockConstraintValidator->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraintTwo);
        $this->mockFactory->shouldReceive('createValidationStage')
            ->with($this->mockConstraintValidator)
            ->andReturn($mockValidationStage)
            ->once();
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockValidationStage)
            ->once();

        $this->builder->addValidationConstraint(
            'foo',
            $mockConstraintOne
        )->addValidationConstraint(
            'foo',
            $mockConstraintTwo
        );
        $this->builder->build();
    }

    public function testAddTransformationChange()
    {
        $mockChange = \Mockery::mock(Change::class);
        $mockTransformationStage = \Mockery::mock(TransformationStage::class);

        $this->mockTransformer->shouldReceive('addFieldChange')
            ->with('foo', $mockChange)
            ->once();
        $this->mockFactory->shouldReceive('createTransformationStage')
            ->with($this->mockTransformer)
            ->andReturn($mockTransformationStage);
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockTransformationStage)
            ->once();

        $this->builder->addTransformationChange('foo', $mockChange);
        $this->builder->build();
    }

    public function testAddMultipleTransformationChange()
    {
        $mockChangeOne = \Mockery::mock(Change::class);
        $mockChangeTwo = \Mockery::mock(Change::class);
        $mockTransformationStage = \Mockery::mock(TransformationStage::class);

        $this->mockTransformer->shouldReceive('addFieldChange')
            ->with('foo', $mockChangeOne)
            ->once();
        $this->mockTransformer->shouldReceive('addFieldChange')
            ->with('foo', $mockChangeTwo)
            ->once();
        $this->mockFactory->shouldReceive('createTransformationStage')
            ->with($this->mockTransformer)
            ->andReturn($mockTransformationStage)
            ->once();
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockTransformationStage)
            ->once();

        $this->builder->addTransformationChange('foo', $mockChangeOne);
        $this->builder->addTransformationChange('foo', $mockChangeTwo);
        $this->builder->build();
    }

    public function testAddFiltrationConstraint()
    {
        $mockConstraint = \Mockery::mock(Constraint::class);
        $mockFiltrationStage = \Mockery::mock(FiltrationStage::class);

        $this->mockConstraintFilter->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraint)
            ->once();
        $this->mockFactory->shouldReceive('createFiltrationStage')
            ->with($this->mockConstraintFilter)
            ->andReturn($mockFiltrationStage);
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockFiltrationStage)
            ->once();

        $this->builder->addFiltrationConstraint(
            'foo',
            $mockConstraint
        );
        $this->builder->build();
    }

    public function testAddMultipleFilterConstraints()
    {
        $mockConstraintOne = \Mockery::mock(Constraint::class);
        $mockConstraintTwo = \Mockery::mock(Constraint::class);
        $mockFiltrationStage = \Mockery::mock(FiltrationStage::class);

        $this->mockFactory->shouldReceive('createConstraintFilter')
            ->andReturn($this->mockConstraintFilter)
            ->once();
        $this->mockConstraintFilter->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraintOne);
        $this->mockConstraintFilter->shouldReceive('setFieldConstraints')
            ->with('foo', $mockConstraintTwo);
        $this->mockFactory->shouldReceive('createFiltrationStage')
            ->with($this->mockConstraintFilter)
            ->andReturn($mockFiltrationStage)
            ->once();
        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockFiltrationStage)
            ->once();

        $this->builder->addFiltrationConstraint(
            'foo',
            $mockConstraintOne
        )->addFiltrationConstraint(
            'foo',
            $mockConstraintTwo
        );
        $this->builder->build();
    }

    public function testAddLoader()
    {
        $mockLoader = \Mockery::mock(LoaderInterface::class);

        $mockLoadStage = \Mockery::mock(LoadStage::class);
        $mockFinaliseStage = \Mockery::mock(FinaliseStage::class);

        $this->mockFactory->shouldReceive('createLoadStage')
            ->with($mockLoader)
            ->andReturn($mockLoadStage);
        $this->mockFactory->shouldReceive('createEltFinaliseStage')
            ->with($mockLoader)
            ->andReturn($mockFinaliseStage);

        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockLoadStage)
            ->once();
        $this->mockOuterPipelineBuilder->shouldReceive('add')
            ->with($mockFinaliseStage)
            ->once();

        $this->builder->addLoader($mockLoader);
        $this->builder->build();
    }

    public function testCreateDatabaseLoader()
    {
        $mockPdo = \Mockery::mock(\PDO::class);
        $table = 'foo';
        $fieldMappings = [];
        $batchSize = 10;
        $mockPreCommitDml = \Mockery::mock(PreCommitDmlInterface::class);

        $mockDbLoader = \Mockery::mock(DatabaseLoader::class);

        $this->mockFactory->shouldReceive('createDatabaseLoader')
            ->with(
                $mockPdo,
                $table,
                $fieldMappings,
                $batchSize,
                $mockPreCommitDml
            )->andReturn($mockDbLoader);

        $this->assertSame(
            $mockDbLoader,
            $this->builder->createDatabaseLoader(
                $mockPdo,
                $table,
                $fieldMappings,
                $batchSize,
                $mockPreCommitDml
            )
        );
    }

    /**
     * @dataProvider getViolationAbortTypesTestData
     * @param array $abortTypes
     */
    public function testSetViolationAbortTypes(array $abortTypes)
    {
        $this->mockFactory->shouldReceive('createInvokeExtractionPipeline')
            ->with($this->mockInnerPipeline, $abortTypes)
            ->andReturn($this->mockInvokeExtractionPipeline);

        foreach ($abortTypes as $type) {
            switch ($type) {
                case RecordViolation::class:
                    $this->builder->abortOnRecordViolation();
                    break;
                case FieldViolation::class:
                    $this->builder->abortOnFieldViolation();
                    break;
            }
        }

        $this->builder->build();
    }

    public function getViolationAbortTypesTestData()
    {
        return [
            [[RecordViolation::class]],
            [[FieldViolation::class]],
            [[RecordViolation::class, FieldViolation::class]]
        ];
    }

    public function testAddLoadObserver()
    {
        $mockObserver = \Mockery::mock(StageObserverInterface::class);
        $mockLoader = \Mockery::mock(LoaderInterface::class);
        $mockLoadStage = \Mockery::mock(LoadStage::class);
        $mockFinaliseStage = \Mockery::mock(FinaliseStage::class);

        $this->mockFactory->shouldReceive('createLoadStage')
            ->with($mockLoader)
            ->andReturn($mockLoadStage);
        $this->mockFactory->shouldReceive('createEltFinaliseStage')
            ->with($mockLoader)
            ->andReturn($mockFinaliseStage);
        $mockLoadStage->shouldReceive('attachObserver')
            ->with($mockObserver)
            ->once();

        $this->builder->addLoader($mockLoader)
            ->addLoadObserver($mockObserver)
            ->build();
    }

    public function testAddTransformationObserver()
    {
        $mockObserver = \Mockery::mock(StageObserverInterface::class);
        $mockChange = \Mockery::mock(Change::class);
        $mockTransformer = \Mockery::mock(Transformer::class);
        $mockTransformationStage = \Mockery::mock(TransformationStage::class);

        $mockTransformer->shouldIgnoreMissing();
        $this->mockFactory->shouldReceive('createTransformer')
            ->andReturn($mockTransformer);
        $this->mockFactory->shouldReceive('createTransformationStage')
            ->with($mockTransformer)
            ->andReturn($mockTransformationStage);
        $mockTransformationStage->shouldReceive('attachObserver')
            ->with($mockObserver)
            ->once();

        $this->builder->addTransformationChange('foo', $mockChange)
            ->addTransformationObserver($mockObserver)
            ->build();
    }

    public function testAddValidationObserver()
    {
        $this->markTestIncomplete();
    }

    public function testAddAllStagesObserver()
    {
        $this->markTestIncomplete();
    }
}
