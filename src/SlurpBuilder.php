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

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\DmlStmtInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use PDO;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraint;

class SlurpBuilder
{
    /**
     * @var PipelineBuilder
     */
    private $outerPipelineBuilder;

    /**
     * @var PipelineBuilder
     */
    private $innerPipelineBuilder;

    /**
     * @var SlurpFactory
     */
    private $factory;

    /**
     * @var ValidationStage
     */
    protected $validationStage;

    /**
     * @var TransformationStage
     */
    protected $transformationStage;

    /**
     * @var FiltrationStage
     */
    protected $filtrationStage;

    /**
     * @var LoadStage[]
     */
    protected $loadStages = [];

    /**
     * @var FinaliseStage[]
     */
    protected $finaliseStages = [];

    /**
     * @var Schema
     */
    protected $tableSchema;

    /**
     * @var bool
     */
    protected $tableSchemaValidateOnly = false;

    /**
     * @var SchemaValidator
     */
    protected $schemaValidator;

    /**
     * @var SchemaTransformer
     */
    protected $schemaTransformer;

    /**
     * @var ConstraintValidator
     */
    protected $constraintValidator;

    /**
     * @var Transformer
     */
    protected $transformer;

    /**
     * @var ConstraintFilter
     */
    protected $constraintFilter;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var callable
     */
    protected $extractionInterrupt;

    public function __construct(
        PipelineBuilder $innerPipelineBuilder,
        PipelineBuilder $outerPipelineBuilder,
        SlurpFactory $slurpFactory
    ) {
        $this->innerPipelineBuilder = $innerPipelineBuilder;
        $this->outerPipelineBuilder = $outerPipelineBuilder;
        $this->factory = $slurpFactory;
    }

    public static function create(): self
    {
        return new static(
            new PipelineBuilder(),
            new PipelineBuilder(),
            new SlurpFactory()
        );
    }

    /**
     * @return SlurpBuilder
     */
    public function setTableSchema(Schema $tableSchema, bool $validateOnly = false): self
    {
        $this->tableSchema = $tableSchema;
        $this->tableSchemaValidateOnly = $validateOnly;

        return $this;
    }

    /**
     * @throws Exception\FactoryException
     */
    public function createTableSchemaFromPath(string $path): Schema
    {
        return $this->factory->createTableSchemaFromPath($path);
    }

    /**
     * @throws Exception\FactoryException
     */
    public function createTableSchemaFromArray(array $arr): Schema
    {
        return $this->factory->createTableSchemaFromArray($arr);
    }

    public function addValidationConstraint(string $field, Constraint $constraint): self
    {
        if (!isset($this->constraintValidator)) {
            $this->constraintValidator = $this->factory->createConstraintValidator();
            $this->validationStage = $this->factory->createValidationStage($this->constraintValidator);
        }

        $this->constraintValidator->setFieldConstraints($field, $constraint);

        return $this;
    }

    public function addTransformationChange(string $field, Change $change): self
    {
        if (!isset($this->transformer)) {
            $this->transformer = $this->factory->createTransformer();
            $this->transformationStage = $this->factory->createTransformationStage($this->transformer);
        }

        $this->transformer->addFieldChange($field, $change);

        return $this;
    }

    public function addFiltrationConstraint(string $field, Constraint $constraint): self
    {
        if (!isset($this->constraintFilter)) {
            $this->constraintFilter = $this->factory->createConstraintFilter();
            $this->filtrationStage = $this->factory->createFiltrationStage($this->constraintFilter);
        }

        $this->constraintFilter->setFieldConstraints($field, $constraint);

        return $this;
    }

    public function addLoader(LoaderInterface $loader): self
    {
        $this->loadStages[] = $this->factory->createLoadStage($loader);
        $this->finaliseStages[] = $this->factory->createEltFinaliseStage($loader);

        return $this;
    }

    /**
     * @param array $fieldMappings array key is the destination column and the array value is the source column
     */
    public function createDatabaseLoader(
        PDO $pdo,
        string $table,
        array $fieldMappings,
        int $batchSize = 100,
        DmlStmtInterface $preCommitStmt = null,
        string $database = null
    ): DatabaseLoader {
        return $this->factory->createDatabaseLoader(
            $pdo,
            $table,
            $fieldMappings,
            $batchSize,
            $preCommitStmt,
            $database
        );
    }

    public function setExtractionInterrupt(callable $interrupt): self
    {
        $this->extractionInterrupt = $interrupt;

        return $this;
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;

        return $this;
    }

    public function build(): Slurp
    {
        if (isset($this->filtrationStage)) {
            if (isset($this->eventDispatcher)) {
                $this->filtrationStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->innerPipelineBuilder->add($this->filtrationStage);
        }

        if (isset($this->tableSchema)) {
            $validationStage = $this->factory->createValidationStage(
                $this->factory->createSchemaValidator($this->tableSchema)
            );

            if (isset($this->eventDispatcher)) {
                $validationStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->innerPipelineBuilder->add($validationStage);

            if (!$this->tableSchemaValidateOnly) {
                $transformationStage = $this->factory->createTransformationStage(
                    $this->factory->createSchemaTransformer($this->tableSchema)
                );

                if (isset($this->eventDispatcher)) {
                    $transformationStage->setEventDispatcher($this->eventDispatcher);
                }

                $this->innerPipelineBuilder->add($transformationStage);
            }
        }

        if (isset($this->validationStage)) {
            if (isset($this->eventDispatcher)) {
                $this->validationStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->innerPipelineBuilder->add($this->validationStage);
        }

        if (isset($this->transformationStage)) {
            if (isset($this->eventDispatcher)) {
                $this->transformationStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->innerPipelineBuilder->add($this->transformationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            if (isset($this->eventDispatcher)) {
                $loadStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->innerPipelineBuilder->add($loadStage);
        }

        $invokeStage = $this->factory->createExtractionStage(
            $this->innerPipelineBuilder->build(
                $this->factory->createInnerProcessor()
            ),
            $this->extractionInterrupt
        );

        if (isset($this->eventDispatcher)) {
            $invokeStage->setEventDispatcher($this->eventDispatcher);
        }

        $this->outerPipelineBuilder->add($invokeStage);

        foreach ($this->finaliseStages as $etlFinaliseStage) {
            if (isset($this->eventDispatcher)) {
                $etlFinaliseStage->setEventDispatcher($this->eventDispatcher);
            }

            $this->outerPipelineBuilder->add($etlFinaliseStage);
        }

        return $this->factory->createSlurp(
            $this->outerPipelineBuilder->build(
                $this->factory->createOuterProcessor()
            )
        );
    }
}
