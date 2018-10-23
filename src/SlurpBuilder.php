<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\InterruptibleProcessor;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitDmlInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\InvokePipelineStage;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\OuterPipeline\OuterStageObserverInterface;
use MilesAsylum\Slurp\InnerPipeline\StageInterface;
use MilesAsylum\Slurp\InnerPipeline\StageObserverInterface;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\FieldViolation;
use MilesAsylum\Slurp\Validate\RecordViolation;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
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
    protected $etlFinaliseStages = [];

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
     * @var StageObserverInterface[]
     */
    protected $allStageObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $validationObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $transformationObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $filtrationObservers = [];

    /**
     * @var StageObserverInterface[]
     */
    protected $loadObservers = [];

    /**
     * @var OuterStageObserverInterface[]
     */
    protected $etlInvokeObservers = [];

    /**
     * @var OuterStageObserverInterface[]
     */
    protected $etlFinaliseObservers = [];

    protected $violationAbortTypes = [];

    /**
     * @var null|callable
     */
    protected $outerProcessInterrupt;

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
     * @param Schema $tableSchema
     * @param bool $validateOnly True to use the schema to validate only, otherwise values validated and transformed.
     * @return SlurpBuilder
     */
    public function setTableSchema(Schema $tableSchema, bool $validateOnly = false): self
    {
        $this->schemaValidator = $this->factory->createSchemaValidator($tableSchema);

        if (!$validateOnly) {
            $this->schemaTransformer = $this->factory->createSchemaTransformer($tableSchema);
        }

        return $this;
    }

    /**
     * @param string $path
     * @return Schema
     * @throws Exception\FactoryException
     */
    public function createTableSchemaFromPath(string $path): Schema
    {
        return $this->factory->createTableSchemaFromPath($path);
    }

    /**
     * @param array $arr
     * @return Schema
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
        $this->etlFinaliseStages[] = $this->factory->createEltFinaliseStage($loader);

        return $this;
    }

    /**
     * @param \PDO $pdo
     * @param string $table
     * @param array $fieldMappings Array key is the destination column and the array value is the source column.
     * @param int $batchSize
     * @param PreCommitDmlInterface|null $preCommitDml
     * @return DatabaseLoader
     */
    public function createDatabaseLoader(
        \PDO $pdo,
        string $table,
        array $fieldMappings,
        int $batchSize,
        PreCommitDmlInterface $preCommitDml = null
    ): DatabaseLoader {
        return $this->factory->createDatabaseLoader(
            $pdo,
            $table,
            $fieldMappings,
            $batchSize,
            $preCommitDml
        );
    }

    public function abortOnRecordViolation()
    {
        $this->violationAbortTypes[RecordViolation::class] = true;
    }

    public function abortOnFieldViolation()
    {
        $this->violationAbortTypes[FieldViolation::class] = true;
    }

    /**
     * Set a function to determine if the outer process should be aborted.
     * @param callable $interrupt The function will be passed an instance of
     * \MilesAsylum\Slurp\Slurp and should return true to abort the process,
     * otherwise false.
     */
    public function setOuterProcessInterrupt(callable $interrupt)
    {
        $this->outerProcessInterrupt = $interrupt;
    }

    public function addAllStagesObserver(StageObserverInterface $observer): self
    {
        $this->allStageObservers[] = $observer;

        return $this;
    }

    public function addValidationObserver(StageObserverInterface $observer): self
    {
        $this->validationObservers[] = $observer;

        return $this;
    }

    public function addTransformationObserver(StageObserverInterface $observer): self
    {
        $this->transformationObservers[] = $observer;

        return $this;
    }

    public function addFiltrationObserver(StageObserverInterface $observer): self
    {
        $this->filtrationObservers[] = $observer;

        return $this;
    }

    public function addLoadObserver(StageObserverInterface $observer): self
    {
        $this->loadObservers[] = $observer;

        return $this;
    }

    public function addEltInvokeObserver(OuterStageObserverInterface $observer): self
    {
        $this->etlInvokeObservers[] = $observer;

        return $this;
    }

    public function addEltFinaliseObserver(OuterStageObserverInterface $observer): self
    {
        $this->etlFinaliseObservers[] = $observer;

        return $this;
    }

    public function build(): Slurp
    {
        if (isset($this->filtrationStage)) {
            $this->attachFiltrationObservers($this->filtrationStage);
            $this->innerPipelineBuilder->add($this->filtrationStage);
        }

        if (isset($this->schemaValidator)) {
            $vs = $this->factory->createValidationStage($this->schemaValidator);
            $this->attachValidationObservers($vs);
            $this->innerPipelineBuilder->add($vs);
        }

        if (isset($this->schemaTransformer)) {
            $ts = $this->factory->createTransformationStage($this->schemaTransformer);
            $this->attachTransformationObservers($ts);
            $this->innerPipelineBuilder->add($ts);
        }

        if (isset($this->validationStage)) {
            $this->attachValidationObservers($this->validationStage);
            $this->innerPipelineBuilder->add($this->validationStage);
        }

        if (isset($this->transformationStage)) {
            $this->attachTransformationObservers($this->transformationStage);
            $this->innerPipelineBuilder->add($this->transformationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            $this->attachLoadObservers($loadStage);
            $this->innerPipelineBuilder->add($loadStage);
        }

        $invokeStage = $this->factory->createEtlInvokePipelineStage(
            $this->innerPipelineBuilder->build(
                $this->factory->createInnerProcessor()
            ),
            array_keys($this->violationAbortTypes)
        );
        $this->attachEtlInvokePipelineObservers($invokeStage);

        $this->outerPipelineBuilder->add($invokeStage);

        foreach ($this->etlFinaliseStages as $etlFinaliseStage) {
            $this->attachEtlFinaliseObservers($etlFinaliseStage);
            $this->outerPipelineBuilder->add($etlFinaliseStage);
        }

        return $this->factory->createSlurp(
            $this->outerPipelineBuilder->build(
                $this->factory->createOuterProcessor($this->outerProcessInterrupt)
            )
        );
    }

    protected function attachEtlInvokePipelineObservers(InvokePipelineStage $extractionPipeline)
    {
        foreach ($this->etlInvokeObservers as $observer) {
            $extractionPipeline->attachObserver($observer);
        }
    }

    protected function attachEtlFinaliseObservers(FinaliseStage $etlFinalise)
    {
        foreach ($this->etlFinaliseObservers as $observer) {
            $etlFinalise->attachObserver($observer);
        }
    }

    protected function attachValidationObservers(ValidationStage $validationStage)
    {
        foreach ($this->validationObservers as $observer) {
            $validationStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($validationStage);
    }

    protected function attachTransformationObservers(TransformationStage $transformationStage)
    {
        foreach ($this->transformationObservers as $observer) {
            $transformationStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($transformationStage);
    }

    protected function attachFiltrationObservers(FiltrationStage $filtrationStage)
    {
        foreach ($this->filtrationObservers as $observer) {
            $filtrationStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($filtrationStage);
    }

    protected function attachLoadObservers(LoadStage $loadStage)
    {
        foreach ($this->loadObservers as $observer) {
            $loadStage->attachObserver($observer);
        }

        $this->attachAllStageObservers($loadStage);
    }

    protected function attachAllStageObservers(StageInterface $stage)
    {
        foreach ($this->allStageObservers as $observer) {
            $stage->attachObserver($observer);
        }
    }
}
