<?php
/**
 * Author: Courtney Miles
 * Date: 24/09/18
 * Time: 5:20 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpFactory;
use MilesAsylum\Slurp\Stage\FinaliseStage;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\RecordViolation;
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
            $this->factory->createFinaliseStage(
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
            InvokeExtractionPipeline::class,
            $this->factory->createInvokeExtractionPipeline(
                \Mockery::mock(PipelineInterface::class)
            )
        );
    }

    public function testCreateInvokeExtractionPipelineWithViolationAbortTypes()
    {
        $this->assertInstanceOf(
            InvokeExtractionPipeline::class,
            $this->factory->createInvokeExtractionPipeline(
                \Mockery::mock(PipelineInterface::class),
                [RecordViolation::class]
            )
        );
    }
}
