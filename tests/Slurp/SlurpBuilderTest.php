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

namespace MilesAsylum\Slurp\Tests\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\InnerPipeline\InnerProcessor;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\DmlStmtInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\OuterProcessor;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpBuilder;
use MilesAsylum\Slurp\SlurpFactory;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraint;

class SlurpBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SlurpBuilder
     */
    private $builder;

    /**
     * @var PipelineBuilder|MockInterface
     */
    private $mockInnerPipelineBuilder;

    /**
     * @var PipelineBuilder|MockInterface
     */
    private $mockOuterPipelineBuilder;

    /**
     * @var SlurpFactory|MockInterface
     */
    private $mockFactory;

    /**
     * @var ConstraintValidator|MockInterface
     */
    private $mockConstraintValidator;

    /**
     * @var Transformer|MockInterface
     */
    private $mockTransformer;

    /**
     * @var ConstraintFilter|MockInterface
     */
    private $mockConstraintFilter;

    /**
     * @var PipelineInterface|MockInterface
     */
    private $mockInnerPipeline;


    /**
     * @var PipelineInterface|MockInterface
     */
    private $mockOuterPipeline;

    /**
     * @var ExtractionStage|MockInterface
     */
    private $mockExtractionStage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockInnerPipeline = $this->createMockPipeline();
        $this->mockOuterPipeline = $this->createMockPipeline();
        $this->mockInnerPipelineBuilder = $this->createMockInnerPipelineBuilder($this->mockInnerPipeline);
        $this->mockOuterPipelineBuilder = $this->createMockOuterPipelineBuilder($this->mockOuterPipeline);
        $this->mockConstraintValidator = $this->createMockConstraintValidator();
        $this->mockTransformer = $this->createMockTransformer();
        $this->mockConstraintFilter = $this->createMockConstraintFilter();
        $this->mockExtractionStage = $this->createMockExtractionStage();

        $this->mockFactory = $this->createMockFactory(
            $this->mockInnerPipeline,
            $this->mockOuterPipeline,
            $this->createMockSlurp(),
            $this->mockExtractionStage,
            $this->mockConstraintValidator,
            $this->mockTransformer,
            $this->mockConstraintFilter,
            $this->createMockOuterProcessor(),
            $this->createMockInnerProcessor()
        );

        $this->builder = new SlurpBuilder(
            $this->mockInnerPipelineBuilder,
            $this->mockOuterPipelineBuilder,
            $this->mockFactory
        );
    }

    public function testCreateBuilder(): void
    {
        $this->assertInstanceOf(
            SlurpBuilder::class,
            SlurpBuilder::create()
        );
    }

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            Slurp::class,
            $this->builder->build()
        );
    }

    public function testSetTableSchema(): void
    {
        $mockTableSchema = Mockery::mock(Schema::class);
        $mockValidationStage = Mockery::mock(ValidationStage::class);
        $mockTransformationStage = $this->createMockTransformationStage();
        $mockSchemaValidator = Mockery::mock(SchemaValidator::class);
        $mockSchemaTransformer = Mockery::mock(SchemaTransformer::class);

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

    public function testSetTableSchemaValidateOnly(): void
    {
        $mockTableSchema = Mockery::mock(Schema::class);
        $mockValidationStage = Mockery::mock(ValidationStage::class);
        $mockSchemaValidator = Mockery::mock(SchemaValidator::class);

        $this->mockFactory->shouldReceive('createSchemaValidator')
            ->with($mockTableSchema)
            ->andReturn($mockSchemaValidator);
        $this->mockFactory->shouldReceive('createValidationStage')
            ->with($mockSchemaValidator)
            ->andReturn($mockValidationStage);

        $this->mockInnerPipelineBuilder->shouldReceive('add')
            ->with($mockValidationStage)
            ->once();

        $this->builder->setTableSchema($mockTableSchema, true);
        $this->builder->build();
    }

    public function testCreateTableSchemaFromPath(): void
    {
        $path = '/foo/bar.json';
        $mockTableSchema = Mockery::mock(Schema::class);

        $this->mockFactory->shouldReceive('createTableSchemaFromPath')
            ->with($path)
            ->andReturn($mockTableSchema);

        $this->assertSame($mockTableSchema, $this->builder->createTableSchemaFromPath($path));
    }

    public function testCreateTableSchemaFromArray(): void
    {
        $array = ['foo'];
        $mockTableSchema = Mockery::mock(Schema::class);

        $this->mockFactory->shouldReceive('createTableSchemaFromArray')
            ->with($array)
            ->andReturn($mockTableSchema);

        $this->assertSame($mockTableSchema, $this->builder->createTableSchemaFromArray($array));
    }

    public function testAddValidationConstraint(): void
    {
        $mockConstraint = Mockery::mock(Constraint::class);
        $mockValidationStage = Mockery::mock(ValidationStage::class);

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

    public function testAddMultipleValidationConstraint(): void
    {
        $mockConstraintOne = Mockery::mock(Constraint::class);
        $mockConstraintTwo = Mockery::mock(Constraint::class);
        $mockValidationStage = Mockery::mock(ValidationStage::class);

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

    public function testAddTransformationChange(): void
    {
        $mockChange = Mockery::mock(Change::class);
        $mockTransformationStage = Mockery::mock(TransformationStage::class);

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

    public function testAddMultipleTransformationChange(): void
    {
        $mockChangeOne = Mockery::mock(Change::class);
        $mockChangeTwo = Mockery::mock(Change::class);
        $mockTransformationStage = Mockery::mock(TransformationStage::class);

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

    public function testAddFiltrationConstraint(): void
    {
        $mockConstraint = Mockery::mock(Constraint::class);
        $mockFiltrationStage = Mockery::mock(FiltrationStage::class);

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

    public function testAddMultipleFilterConstraints(): void
    {
        $mockConstraintOne = Mockery::mock(Constraint::class);
        $mockConstraintTwo = Mockery::mock(Constraint::class);
        $mockFiltrationStage = Mockery::mock(FiltrationStage::class);

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

    public function testAddLoader(): void
    {
        $mockLoader = Mockery::mock(LoaderInterface::class);

        $mockLoadStage = $this->createMockLoadStage();
        $mockFinaliseStage = Mockery::mock(FinaliseStage::class);

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

    public function testCreateDatabaseLoader(): void
    {
        $mockPdo = Mockery::mock(\PDO::class);
        $table = 'foo';
        $fieldMappings = [];
        $batchSize = 10;
        $mockPreCommitStmt = Mockery::mock(DmlStmtInterface::class);

        $mockDbLoader = Mockery::mock(DatabaseLoader::class);

        $this->mockFactory->shouldReceive('createDatabaseLoader')
            ->with(
                $mockPdo,
                $table,
                $fieldMappings,
                $batchSize,
                $mockPreCommitStmt,
                null
            )->andReturn($mockDbLoader);

        $this->assertSame(
            $mockDbLoader,
            $this->builder->createDatabaseLoader(
                $mockPdo,
                $table,
                $fieldMappings,
                $batchSize,
                $mockPreCommitStmt
            )
        );
    }

    public function testSetExtractionInterrupt(): void
    {
        $interrupt = static function () {
        };

        $this->mockFactory->shouldReceive('createExtractionStage')
            ->with($this->mockInnerPipeline, $interrupt)
            ->andReturn($this->mockExtractionStage)
            ->once();

        $this->builder->setExtractionInterrupt($interrupt);
        $this->builder->build();
    }

    public function testSetEventDispatcherOnLoadStage(): void
    {
        $mockDispatcher = $this->createMockEventDispatcher();
        $mockLoader = $this->createMockLoader();
        $mockLoadStage = $this->createMockLoadStage();

        $this->mockFactory->shouldReceive('createLoadStage')
            ->with($mockLoader)
            ->andReturn($mockLoadStage);
        $this->mockFactory->shouldReceive('createEltFinaliseStage')
            ->with($mockLoader)
            ->andReturn($this->createMockFinaliseStage());
        $mockLoadStage->shouldReceive('setEventDispatcher')
            ->with($mockDispatcher)
            ->once();

        $this->builder->addLoader($mockLoader)
            ->setEventDispatcher($mockDispatcher)
            ->build();
    }

    public function testSetEventDispatcherOnTransformationStage(): void
    {
        $mockDispatcher = $this->createMockEventDispatcher();
        $mockTransformer = $this->createMockTransformer();
        $mockTransformationStage = $this->createMockTransformationStage();

        $mockTransformer->shouldIgnoreMissing();
        $this->mockFactory->shouldReceive('createTransformer')
            ->andReturn($mockTransformer);
        $this->mockFactory->shouldReceive('createTransformationStage')
            ->with($mockTransformer)
            ->andReturn($mockTransformationStage);
        $mockTransformationStage->shouldReceive('setEventDispatcher')
            ->with($mockDispatcher)
            ->once();

        $this->builder->addTransformationChange('foo', Mockery::mock(Change::class))
            ->setEventDispatcher($mockDispatcher)
            ->build();
    }

    public function testSetEventDispatcherOnValidationStage(): void
    {
        $mockDispatcher = $this->createMockEventDispatcher();
        $mockValidationStage = $this->createMockValidationStage();
        $mockValidationStage->shouldReceive('setEventDispatcher')
            ->with($mockDispatcher)
            ->once();

        $this->mockFactory->shouldReceive('createValidationStage')
            ->andReturn($mockValidationStage)
            ->byDefault();

        $this->builder->addValidationConstraint('foo', Mockery::mock(Constraint::class))
            ->setEventDispatcher($mockDispatcher)
            ->build();
    }

    /**
     * @return ValidationStage|MockInterface
     */
    protected function createMockValidationStage()
    {
        $mockValidationStage = Mockery::mock(ValidationStage::class);

        return $mockValidationStage;
    }

    /**
     * @return LoaderInterface|MockInterface
     */
    protected function createMockLoader()
    {
        $mockLoader = Mockery::mock(LoaderInterface::class);

        return $mockLoader;
    }

    /**
     * @return LoadStage|MockInterface
     */
    protected function createMockLoadStage()
    {
        $mockLoadStage = Mockery::mock(LoadStage::class);

        return $mockLoadStage;
    }

    /**
     * @return TransformationStage|MockInterface
     */
    protected function createMockTransformationStage()
    {
        $mockTransformationStage = Mockery::mock(TransformationStage::class);
        $mockTransformationStage->shouldReceive('setEventDispatcher')->byDefault();

        return $mockTransformationStage;
    }

    /**
     * @param PipelineInterface $innerPipeline
     * @param PipelineInterface $outerPipeline
     * @param Slurp $slurp
     * @param ExtractionStage $extractionStage
     * @param ConstraintValidator $constraintValidator
     * @param Transformer $transformer
     * @param ConstraintFilter $constraintFilter
     * @param OuterProcessor $outerProcessor
     * @param InnerProcessor $innerProcessor
     * @return SlurpFactory|MockInterface
     */
    protected function createMockFactory(
        PipelineInterface $innerPipeline,
        PipelineInterface $outerPipeline,
        Slurp $slurp,
        ExtractionStage $extractionStage,
        ConstraintValidator $constraintValidator,
        Transformer $transformer,
        ConstraintFilter $constraintFilter,
        OuterProcessor $outerProcessor,
        InnerProcessor $innerProcessor
    ) {
        $mockFactory = Mockery::mock(SlurpFactory::class);
        $mockFactory->shouldReceive('createSlurp')
            ->with($outerPipeline)
            ->andReturn($slurp)
            ->byDefault();
        $mockFactory->shouldReceive('createExtractionStage')
            ->with($innerPipeline, null)
            ->andReturn($extractionStage)
            ->byDefault();
        $mockFactory->shouldReceive('createConstraintValidator')
            ->andReturn($constraintValidator)
            ->byDefault();
        $mockFactory->shouldReceive('createTransformer')
            ->andReturn($transformer)
            ->byDefault();
        $mockFactory->shouldReceive('createConstraintFilter')
            ->andReturn($constraintFilter)
            ->byDefault();
        $mockFactory->shouldReceive('createOuterProcessor')
            ->andReturn($outerProcessor)
            ->byDefault();
        $mockFactory->shouldReceive('createInnerProcessor')
            ->andReturn($innerProcessor)
            ->byDefault();

        return $mockFactory;
    }

    /**
     * @param PipelineInterface $innerPipeline
     * @return PipelineBuilder|MockInterface
     */
    protected function createMockInnerPipelineBuilder(PipelineInterface $innerPipeline)
    {
        $mockInnerPipelineBuilder = Mockery::mock(PipelineBuilder::class);
        $mockInnerPipelineBuilder->shouldReceive('add')
            ->byDefault();
        $mockInnerPipelineBuilder->shouldReceive('build')
            ->andReturn($innerPipeline)
            ->byDefault();

        return $mockInnerPipelineBuilder;
    }

    /**
     * @return PipelineInterface|MockInterface
     */
    protected function createMockPipeline()
    {
        $mockPipeline = Mockery::mock(PipelineInterface::class);

        return $mockPipeline;
    }

    /**
     * @param PipelineInterface $outerPipeline
     * @return PipelineBuilder|MockInterface
     */
    protected function createMockOuterPipelineBuilder(PipelineInterface $outerPipeline)
    {
        $mockOuterPipelineBuilder = Mockery::mock(PipelineBuilder::class);

        $mockOuterPipelineBuilder->shouldReceive('add')
            ->byDefault();
        $mockOuterPipelineBuilder->shouldReceive('build')
            ->andReturn($outerPipeline)
            ->byDefault();

        return $mockOuterPipelineBuilder;
    }

    /**
     * @return ExtractionStage|MockInterface
     */
    protected function createMockExtractionStage()
    {
        $mockExtractionStage = Mockery::mock(ExtractionStage::class);
        $mockExtractionStage->shouldReceive('setEventDispatcher')
            ->byDefault();

        return $mockExtractionStage;
    }

    /**
     * @return Slurp|MockInterface
     */
    protected function createMockSlurp()
    {
        $mockSlurp = Mockery::mock(Slurp::class);

        return $mockSlurp;
    }

    /**
     * @return ConstraintValidator|MockInterface
     */
    protected function createMockConstraintValidator()
    {
        $mockConstraintValidator = Mockery::mock(ConstraintValidator::class);
        $mockConstraintValidator->shouldReceive('setFieldConstraints')
            ->byDefault();

        return $mockConstraintValidator;
    }

    /**
     * @return Transformer|MockInterface
     */
    protected function createMockTransformer()
    {
        $mockTransformer = Mockery::mock(Transformer::class);

        return $mockTransformer;
    }

    /**
     * @return ConstraintFilter|MockInterface
     */
    protected function createMockConstraintFilter()
    {
        $mockConstraintFilter = Mockery::mock(ConstraintFilter::class);

        return $mockConstraintFilter;
    }

    /**
     * @return OuterProcessor|MockInterface
     */
    protected function createMockOuterProcessor()
    {
        $mockOuterProcessor = Mockery::mock(OuterProcessor::class);

        return $mockOuterProcessor;
    }

    /**
     * @return InnerProcessor|MockInterface
     */
    protected function createMockInnerProcessor()
    {
        $mockInnerProcessor = Mockery::mock(InnerProcessor::class);

        return$mockInnerProcessor;
    }

    /**
     * @return MockInterface|EventDispatcherInterface
     */
    protected function createMockEventDispatcher()
    {
        return Mockery::mock(EventDispatcherInterface::class);
    }

    /**
     * @return FinaliseStage|MockInterface
     */
    protected function createMockFinaliseStage()
    {
        $mockFinaliseStage = Mockery::mock(FinaliseStage::class);
        $mockFinaliseStage->shouldReceive('setEventDispatcher')
            ->byDefault();

        return $mockFinaliseStage;
    }
}
