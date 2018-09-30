<?php
/**
 * Author: Courtney Miles
 * Date: 16/09/18
 * Time: 9:13 AM
 */

namespace MilesAsylum\Slurp;

use frictionlessdata\tableschema\Schema;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Exception\FactoryException;
use MilesAsylum\Slurp\Load\DatabaseLoader\DatabaseLoader;
use MilesAsylum\Slurp\Load\DatabaseLoader\LoaderFactory;
use MilesAsylum\Slurp\Load\DatabaseLoader\PreCommitDmlInterface;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Stage\FinaliseStage;
use MilesAsylum\Slurp\Stage\InvokeExtractionPipeline;
use MilesAsylum\Slurp\Stage\LoadStage;
use MilesAsylum\Slurp\Stage\TransformationStage;
use MilesAsylum\Slurp\Stage\ValidationStage;
use MilesAsylum\Slurp\Transform\SchemaTransformer\SchemaTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Transformer;
use MilesAsylum\Slurp\Transform\TransformerInterface;
use MilesAsylum\Slurp\Validate\ConstraintValidation\ConstraintValidator;
use MilesAsylum\Slurp\Validate\SchemaValidation\SchemaValidator;
use MilesAsylum\Slurp\Validate\ValidatorInterface;
use Symfony\Component\Validator\Validation;

class SlurpFactory
{
    public function createValidationStage(ValidatorInterface $validator): ValidationStage
    {
        return new ValidationStage($validator);
    }

    public function createTransformationStage(TransformerInterface $transformer): TransformationStage
    {
        return new TransformationStage($transformer);
    }

    public function createLoadStage(LoaderInterface $loader): LoadStage
    {
        return new LoadStage($loader);
    }

    public function createFinaliseStage(LoaderInterface $loader): FinaliseStage
    {
        return new FinaliseStage($loader);
    }

    /**
     * @param string $path
     * @return Schema
     * @throws FactoryException
     */
    public function createTableSchemaFromPath(string $path): Schema
    {
        try {
            return new Schema($path);
        } catch (\Throwable $e) {
            throw new FactoryException(
                'Error creating table schema from file path: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @param array $arr
     * @return Schema
     * @throws FactoryException
     */
    public function createTableSchemaFromArray(array $arr): Schema
    {
        try {
            return new Schema($arr);
        } catch (\Throwable $e) {
            throw new FactoryException(
                'Error creating table schema from array: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    public function createConstraintValidator()
    {
        return new ConstraintValidator(
            Validation::createValidator()
        );
    }

    public function createSchemaValidator(Schema $tableSchema): SchemaValidator
    {
        return new SchemaValidator($tableSchema);
    }

    public function createTransformer()
    {
        return Transformer::createTransformer();
    }

    public function createSchemaTransformer(Schema $tableSchema): SchemaTransformer
    {
        return new SchemaTransformer($tableSchema);
    }

    public function createDatabaseLoader(
        \PDO $pdo,
        string $table,
        array $fieldMappings,
        int $batchSize,
        PreCommitDmlInterface $preCommitDml = null
    ): DatabaseLoader {
        return new DatabaseLoader(
            $table,
            $fieldMappings,
            new LoaderFactory($pdo),
            $batchSize,
            $preCommitDml
        );
    }

    public function createInvokeExtractionPipeline(PipelineInterface $innerPipeline, array $violationAbortTypes = [])
    {
        return new InvokeExtractionPipeline($innerPipeline, $violationAbortTypes);
    }

    public function createSlurp(PipelineInterface $pipeline)
    {
        return new Slurp($pipeline);
    }
}
