<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 8:56 PM
 */

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineBuilder;
use MilesAsylum\Slurp\Filter\ConstraintFiltration\ConstraintFilter;
use MilesAsylum\Slurp\InnerPipeline\FiltrationStage;
use MilesAsylum\Slurp\InnerPipeline\LoadStage;
use MilesAsylum\Slurp\InnerPipeline\StageInterface;
use MilesAsylum\Slurp\InnerPipeline\StageObserverInterface;
use MilesAsylum\Slurp\InnerPipeline\TransformationStage;
use MilesAsylum\Slurp\InnerPipeline\ValidationStage;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitDmlInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\OuterPipeline\ExtractionStage;
use MilesAsylum\Slurp\OuterPipeline\FinaliseStage;
use MilesAsylum\Slurp\OuterPipeline\OuterStageObserverInterface;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
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
    protected $finaliseStages = [];

    /**
     * @var Schema
     */
    protected $tableSchema;

    /**
     * @var bool
     */
    protected $schemaValidationOnly = false;

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
    protected $extractionObservers = [];

    /**
     * @var OuterStageObserverInterface[]
     */
    protected $finaliseObservers = [];

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
     * @param Schema $tableSchema
     * @return SlurpBuilder
     */
    public function setTableSchema(Schema $tableSchema): self
    {
        $this->tableSchema = $tableSchema;

        return $this;
    }

    /**
     * @param $validateOnly
     * @return SlurpBuilder
     */
    public function setSchemaValidationOnly($validateOnly): self
    {
        $this->schemaValidationOnly = $validateOnly;

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
        $this->finaliseStages[] = $this->factory->createEltFinaliseStage($loader);

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
        int $batchSize = 100,
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

    public function setExtractionInterrupt(callable $interrupt): self
    {
        $this->extractionInterrupt = $interrupt;

        return $this;
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

    public function addExtractionObserver(OuterStageObserverInterface $observer): self
    {
        $this->extractionObservers[] = $observer;

        return $this;
    }

    public function addFinaliseObserver(OuterStageObserverInterface $observer): self
    {
        $this->finaliseObservers[] = $observer;

        return $this;
    }

    public function build(): Slurp
    {
        if (isset($this->filtrationStage)) {
            $this->attachFiltrationObservers($this->filtrationStage);
            $this->innerPipelineBuilder->add($this->filtrationStage);
        }

        if (isset($this->tableSchema)) {
            $vs = $this->factory->createValidationStage(
                $this->factory->createSchemaValidator($this->tableSchema)
            );
            $this->attachValidationObservers($vs);
            $this->innerPipelineBuilder->add($vs);

            if (!$this->schemaValidationOnly) {
                $ts = $this->factory->createTransformationStage(
                    $this->factory->createSchemaTransformer($this->tableSchema)
                );
                $this->attachTransformationObservers($ts);
                $this->innerPipelineBuilder->add($ts);
            }
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

        $invokeStage = $this->factory->createExtractionStage(
            $this->innerPipelineBuilder->build(
                $this->factory->createInnerProcessor()
            ),
            $this->extractionInterrupt
        );
        $this->attachExtractionObservers($invokeStage);

        $this->outerPipelineBuilder->add($invokeStage);

        foreach ($this->finaliseStages as $etlFinaliseStage) {
            $this->attachFinaliseObservers($etlFinaliseStage);
            $this->outerPipelineBuilder->add($etlFinaliseStage);
        }

        return $this->factory->createSlurp(
            $this->outerPipelineBuilder->build(
                $this->factory->createOuterProcessor()
            )
        );
    }

    protected function attachExtractionObservers(ExtractionStage $extractionPipeline)
    {
        foreach ($this->extractionObservers as $observer) {
            $extractionPipeline->attachObserver($observer);
        }
    }

    protected function attachFinaliseObservers(FinaliseStage $etlFinalise)
    {
        foreach ($this->finaliseObservers as $observer) {
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
