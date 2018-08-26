<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:34 PM
 */

namespace MilesAsylum\Slurp;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Extract\ExtractorInterface;

class Slurp
{
    /**
     * @var Pipeline
     */
    private $pipeline;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function process(ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
        $this->pipeline->process($this);
        $this->extractor = null;
    }

    public function getExtractor(): ?ExtractorInterface
    {
        return $this->extractor;
    }
}
