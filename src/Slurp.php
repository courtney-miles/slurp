<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:34 PM
 */

namespace MilesAsylum\Slurp;

use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Extract\ExtractorInterface;

class Slurp
{
    /**
     * @var PipelineInterface
     */
    private $pipeline;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @var bool
     */
    private $aborted = false;

    public function __construct(PipelineInterface $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function process(ExtractorInterface $extractor): void
    {
        $this->extractor = $extractor;
        ($this->pipeline)($this);
        $this->extractor = null;
    }

    public function getExtractor(): ?ExtractorInterface
    {
        return $this->extractor;
    }

    public function abort()
    {
        $this->aborted = true;
    }

    public function isAborted()
    {
        return $this->aborted;
    }
}
