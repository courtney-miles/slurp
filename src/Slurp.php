<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:34 PM
 */

namespace MilesAsylum\Slurp;


use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Extract\ExtractorInterface;
use MilesAsylum\Slurp\Transform\Transformer;
use MilesAsylum\Slurp\Validate\Validator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class Slurp
{
    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var string|integer
     */
    private $currentRowKey;

    /**
     * @var ConstraintViolationListInterface
     */
    private $currentRowViolations;

    /**
     * @var array
     */
    private $currentRow;

    public function __construct(
        ExtractorInterface $extractor,
        LoaderInterface $loader,
        Validator $validator,
        Transformer $transformer
    ) {
        $this->extractor = $extractor;
        $this->loader = $loader;
        $this->validator = $validator;
        $this->transformer = $transformer;
    }

    public function getExtractor()
    {
        return $this->extractor;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function load()
    {
        foreach ($this->extractor as $rowId => $row) {
            $this->currentRowKey = $rowId;
            $this->currentRowViolations = $this->validator->validateRow($row, $rowId);

            if (!count($this->currentRowViolations)) {
                $this->currentRow = $this->transformer->transformRow($row);
            }

            $this->loader->update($this);

            $this->currentRowKey = $this->currentRow = $this->currentRowViolations = null;
        }
    }

    public function grabRow()
    {
        return $this->currentRow;
    }

    public function grabRowKey()
    {
        return $this->currentRowKey;
    }

    public function grabRowViolations()
    {
        return $this->currentRowViolations;
    }
}