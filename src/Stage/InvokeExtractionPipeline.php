<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

namespace MilesAsylum\Slurp\Stage;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;

class InvokeExtractionPipeline implements OuterProcessStageInterface
{
    /**
     * @var Pipeline
     */
    private $innerPipeline;

    /**
     * @var array
     */
    private $violationAbortTypes = [];

    /**
     * InvokeExtractionPipeline constructor.
     * @param PipelineInterface $innerPipeline
     * @param array $violationAbortTypes Violation types that should cause extraction to abort.
     */
    public function __construct(PipelineInterface $innerPipeline, array $violationAbortTypes = [])
    {
        $this->innerPipeline = $innerPipeline;
        $this->violationAbortTypes = $violationAbortTypes;
    }

    public function __invoke(Slurp $slurp): Slurp
    {
        foreach ($slurp->getExtractor() as $id => $values) {
            $payload = new SlurpPayload();
            $payload->setRecordId($id);
            $payload->setRecord($values);

            ($this->innerPipeline)($payload);

            foreach ($this->violationAbortTypes as $abortType) {
                if ($payload->hasViolations($abortType)) {
                    break 2;
                }
            }
        }

        return $slurp;
    }
}
