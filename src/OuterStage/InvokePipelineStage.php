<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

namespace MilesAsylum\Slurp\OuterStage;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;

class InvokePipelineStage extends AbstractOuterStage
{
    /**
     * @var Pipeline
     */
    private $innerPipeline;

    /**
     * @var array
     */
    private $violationAbortTypes = [];

    const STATE_RECORD_PROCESSED = 'record-processed';
    const STATE_ABORTED = 'aborted';

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
        $this->notify(self::STATE_BEGIN);

        foreach ($slurp->getExtractor() as $id => $values) {
            $payload = new SlurpPayload();
            $payload->setRecordId($id);
            $payload->setRecord($values);

            ($this->innerPipeline)($payload);

            $this->notify(self::STATE_RECORD_PROCESSED);

            foreach ($this->violationAbortTypes as $abortType) {
                if ($payload->hasViolations($abortType)) {
                    $this->notify(self::STATE_ABORTED);
                    break 2;
                }
            }
        }

        $this->notify(self::STATE_END);

        return $slurp;
    }
}
