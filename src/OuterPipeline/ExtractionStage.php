<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

namespace MilesAsylum\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Event\ExtractionAbortedEvent;
use MilesAsylum\Slurp\Event\ExtractionEndedEvent;
use MilesAsylum\Slurp\Event\ExtractionStartedEvent;
use MilesAsylum\Slurp\Event\RecordProcessedEvent;
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
        $this->dispatch(ExtractionStartedEvent::NAME, new ExtractionStartedEvent());

        foreach ($slurp->getExtractor() as $id => $values) {
            $payload = new SlurpPayload();
            $payload->setRecordId($id);
            $payload->setRecord($values);

            ($this->innerPipeline)($payload);

            $this->dispatch(RecordProcessedEvent::NAME, new RecordProcessedEvent());

            $interrupt = $this->interrupt;

            if ($interrupt !== null && $interrupt($slurp, $payload)) {
                $slurp->abort();
                $this->dispatch(ExtractionAbortedEvent::NAME, new ExtractionAbortedEvent());
                break;
            }
        }

        $this->dispatch(ExtractionEndedEvent::NAME, new ExtractionEndedEvent());

        return $slurp;
    }
}
