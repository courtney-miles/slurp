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
use MilesAsylum\Slurp\InnerStage\FiltrationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitDmlInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterStage\InvokePipelineStage;
use MilesAsylum\Slurp\OuterStage\FinaliseStage;
use MilesAsylum\Slurp\InnerStage\LoadStage;
use MilesAsylum\Slurp\OuterStage\OuterStageObserverInterface;
use MilesAsylum\Slurp\InnerStage\StageInterface;
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
     * @var ValidationStage[]
     */
    protected $validationStages = [];

    /**
     * @var TransformationStage[]
     */
    protected $transformationStages = [];

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

    public function setTableSchema(Schema $tableSchema): self
    {
        $this->schemaValidator = $this->factory->createSchemaValidator($tableSchema);
        $this->schemaTransformer = $this->factory->createSchemaTransformer($tableSchema);

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
            $this->validationStages[] = $this->factory->createValidationStage($this->constraintValidator);
        }

        $this->constraintValidator->setFieldConstraints($field, $constraint);

        return $this;
    }

    public function addTransformationChange(string $field, Change $change): self
    {
        if (!isset($this->transformer)) {
            $this->transformer = $this->factory->createTransformer();
            $this->transformationStages[] = $this->factory->createTransformationStage($this->transformer);
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

        foreach ($this->validationStages as $validationStage) {
            $this->attachValidationObservers($validationStage);
            $this->innerPipelineBuilder->add($validationStage);
        }

        foreach ($this->transformationStages as $transformationStage) {
            $this->attachTransformationObservers($transformationStage);
            $this->innerPipelineBuilder->add($transformationStage);
        }

        if (isset($this->filtrationStage)) {
            $this->attachFiltrationObservers($this->filtrationStage);
            $this->innerPipelineBuilder->add($this->filtrationStage);
        }

        foreach ($this->loadStages as $loadStage) {
            $this->attachLoadObservers($loadStage);
            $this->innerPipelineBuilder->add($loadStage);
        }

        $invokeStage = $this->factory->createEtlInvokePipelineStage(
            $this->innerPipelineBuilder->build(
                $this->factory->createInnerProcessor()
            )
        );
        $this->attachEtlInvokePipelineObservers($invokeStage);

        $this->outerPipelineBuilder->add($invokeStage);

        foreach ($this->etlFinaliseStages as $etlFinaliseStage) {
            $this->attachEtlFinaliseObservers($etlFinaliseStage);
            $this->outerPipelineBuilder->add($etlFinaliseStage);
        }

        return $this->factory->createSlurp(
            $this->outerPipelineBuilder->build(
                $this->factory->createOuterProcessor()
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
