<?php
/**
 * Author: Courtney Miles
 * Date: 24/09/18
 * Time: 5:20 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\InnerProcessor;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\SimpleDeleteStmt;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\OuterProcessor;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpFactory;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class SlurpFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SlurpFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->factory = new SlurpFactory();
    }

    public function testCreateTransformationStage()
    {
        $this->assertInstanceOf(
            TransformationStage::class,
            $this->factory->createTransformationStage(\Mockery::mock(TransformerInterface::class))
        );
    }

    public function testCreateSlurp()
    {
        $this->assertInstanceOf(
            Slurp::class,
            $this->factory->createSlurp(\Mockery::mock(PipelineInterface::class))
        );
    }

    public function testCreateConstraintValidator()
    {
        $this->assertInstanceOf(
            ConstraintValidator::class,
            $this->factory->createConstraintValidator()
        );
    }

    public function testCreateSchemaValidator()
    {
        $this->assertInstanceOf(
            SchemaValidator::class,
            $this->factory->createSchemaValidator(
                \Mockery::mock(Schema::class)
            )
        );
    }

    public function testCreateTransformer()
    {
        $this->assertInstanceOf(
            Transformer::class,
            $this->factory->createTransformer()
        );
    }

    public function testCreateConstraintFilter()
    {
        $this->assertInstanceOf(
            ConstraintFilter::class,
            $this->factory->createConstraintFilter()
        );
    }

    public function testCreateSchemaTransformer()
    {
        $this->assertInstanceOf(
            SchemaTransformer::class,
            $this->factory->createSchemaTransformer(
                \Mockery::mock(Schema::class)
            )
        );
    }

    public function testCreateTableSchemaFromPath()
    {
        $this->assertInstanceOf(
            Schema::class,
            $this->factory->createTableSchemaFromPath(
                __DIR__ . '/_fixtures/slurp_factory_test_schema.json'
            )
        );
    }

    public function testCreateTableSchemaFromArray()
    {
        $this->assertInstanceOf(
            Schema::class,
            $this->factory->createTableSchemaFromArray(
                ['fields' => [['name' => 'foo']]]
            )
        );
    }

    public function testCreateLoadStage()
    {
        $this->assertInstanceOf(
            LoadStage::class,
            $this->factory->createLoadStage(\Mockery::mock(LoaderInterface::class)),
            \Mockery::mock(LoaderInterface::class)
        );
    }

    public function testCreateFinaliseLoadStage()
    {
        $this->assertInstanceOf(
            FinaliseStage::class,
            $this->factory->createEltFinaliseStage(
                \Mockery::mock(LoaderInterface::class)
            )
        );
    }

    public function testCreateDatabaseLoader()
    {
        $this->assertInstanceOf(
            DatabaseLoader::class,
            $this->factory->createDatabaseLoader(
                \Mockery::mock(\PDO::class),
                'foo',
                [],
                10
            )
        );
    }

    public function testCreateValidationStage()
    {
        $this->assertInstanceOf(
            ValidationStage::class,
            $this->factory->createValidationStage(
                \Mockery::mock(ValidatorInterface::class)
            )
        );
    }

    public function testCreateInvokeExtractionPipeline()
    {
        $this->assertInstanceOf(
            ExtractionStage::class,
            $this->factory->createExtractionStage(
                \Mockery::mock(PipelineInterface::class)
            )
        );
    }

    public function testCreateInvokeExtractionPipelineWithViolationAbortTypes()
    {
        $this->assertInstanceOf(
            ExtractionStage::class,
            $this->factory->createExtractionStage(
                \Mockery::mock(PipelineInterface::class)
            )
        );
    }

    public function testCreateInnerProcess()
    {
        $this->assertInstanceOf(
            InnerProcessor::class,
            $this->factory->createInnerProcessor()
        );
    }

    public function testCreateOuterProcess()
    {
        $this->assertInstanceOf(
            OuterProcessor::class,
            $this->factory->createOuterProcessor()
        );
    }

    public function testCreateSimpleDeleteStmt()
    {
        $this->assertInstanceOf(
            SimpleDeleteStmt::class,
            $this->factory->createSimpleDeleteStmt(
                \Mockery::mock(\PDO::class),
                'my_tbl',
                [],
                'my_db'
            )
        );
    }
}
