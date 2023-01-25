<?php

/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @license MIT
 */
declare(strict_types=1);

namespace MilesAsylum\Slurp\Tests\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\InnerProcessor;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\SimpleDeleteStmt;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\OuterPipeline\OuterProcessor;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpFactory;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SlurpFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SlurpFactory
     */
    protected $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new SlurpFactory();
    }

    public function testCreateTransformationStage(): void
    {
        self::assertInstanceOf(
            TransformationStage::class,
            $this->factory->createTransformationStage(Mockery::mock(TransformerInterface::class))
        );
    }

    public function testCreateSlurp(): void
    {
        self::assertInstanceOf(
            Slurp::class,
            $this->factory->createSlurp(Mockery::mock(PipelineInterface::class))
        );
    }

    public function testCreateConstraintValidator(): void
    {
        self::assertInstanceOf(
            ConstraintValidator::class,
            $this->factory->createConstraintValidator()
        );
    }

    public function testCreateSchemaValidator(): void
    {
        self::assertInstanceOf(
            SchemaValidator::class,
            $this->factory->createSchemaValidator(
                Mockery::mock(Schema::class)
            )
        );
    }

    public function testCreateTransformer(): void
    {
        self::assertInstanceOf(
            Transformer::class,
            $this->factory->createTransformer()
        );
    }

    public function testCreateConstraintFilter(): void
    {
        self::assertInstanceOf(
            ConstraintFilter::class,
            $this->factory->createConstraintFilter()
        );
    }

    public function testCreateSchemaTransformer(): void
    {
        self::assertInstanceOf(
            SchemaTransformer::class,
            $this->factory->createSchemaTransformer(
                Mockery::mock(Schema::class)
            )
        );
    }

    public function testCreateTableSchemaFromPath(): void
    {
        self::assertInstanceOf(
            Schema::class,
            $this->factory->createTableSchemaFromPath(
                __DIR__.'/_fixtures/slurp_factory_test_schema.json'
            )
        );
    }

    public function testCreateTableSchemaFromArray(): void
    {
        self::assertInstanceOf(
            Schema::class,
            $this->factory->createTableSchemaFromArray(
                ['fields' => [['name' => 'foo']]]
            )
        );
    }

    public function testCreateLoadStage(): void
    {
        self::assertInstanceOf(
            LoadStage::class,
            $this->factory->createLoadStage(
                Mockery::mock(LoaderInterface::class)
            )
        );
    }

    public function testCreateFinaliseLoadStage(): void
    {
        self::assertInstanceOf(
            FinaliseStage::class,
            $this->factory->createEltFinaliseStage(
                Mockery::mock(LoaderInterface::class)
            )
        );
    }

    public function testCreateDatabaseLoader(): void
    {
        self::assertInstanceOf(
            DatabaseLoader::class,
            $this->factory->createDatabaseLoader(
                Mockery::mock(\PDO::class),
                'foo',
                [],
                10
            )
        );
    }

    public function testCreateValidationStage(): void
    {
        self::assertInstanceOf(
            ValidationStage::class,
            $this->factory->createValidationStage(
                Mockery::mock(ValidatorInterface::class)
            )
        );
    }

    public function testCreateInvokeExtractionPipeline(): void
    {
        self::assertInstanceOf(
            ExtractionStage::class,
            $this->factory->createExtractionStage(
                Mockery::mock(PipelineInterface::class)
            )
        );
    }

    public function testCreateInvokeExtractionPipelineWithViolationAbortTypes(): void
    {
        self::assertInstanceOf(
            ExtractionStage::class,
            $this->factory->createExtractionStage(
                Mockery::mock(PipelineInterface::class)
            )
        );
    }

    public function testCreateInnerProcess(): void
    {
        self::assertInstanceOf(
            InnerProcessor::class,
            $this->factory->createInnerProcessor()
        );
    }

    public function testCreateOuterProcess(): void
    {
        self::assertInstanceOf(
            OuterProcessor::class,
            $this->factory->createOuterProcessor()
        );
    }

    public function testCreateSimpleDeleteStmt(): void
    {
        self::assertInstanceOf(
            SimpleDeleteStmt::class,
            $this->factory->createSimpleDeleteStmt(
                Mockery::mock(\PDO::class),
                'my_tbl',
                [],
                'my_db'
            )
        );
    }
}
