<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

namespace MilesAsylum\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;

class ExtractionStage extends AbstractOuterStage
{
    /**
     * @var Pipeline
     */
    private $innerPipeline;

    /**
     * @var callable
     */
    private $interrupt;

    const STATE_RECORD_PROCESSED = 'record-processed';
    const STATE_ABORTED = 'aborted';

    /**
     * InvokeExtractionPipeline constructor.
     * @param PipelineInterface $innerPipeline
     * @param callable|null $interrupt
     */
    public function __construct(PipelineInterface $innerPipeline, callable $interrupt = null)
    {
        $this->innerPipeline = $innerPipeline;
        $this->interrupt = $interrupt;
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

            $interrupt = $this->interrupt;

            if ($interrupt !== null && $interrupt($payload)) {
                $slurp->abort();
                $this->notify(self::STATE_ABORTED);
                break;
            }
        }

        $this->notify(self::STATE_END);

        return $slurp;
    }
}
