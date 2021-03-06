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

    public function abort(): void
    {
        $this->aborted = true;
    }

    public function isAborted(): bool
    {
        return $this->aborted;
    }
}
