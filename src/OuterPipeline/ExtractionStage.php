<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\OuterPipeline;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Event\ExtractionAbortedEvent;
use MilesAsylum\Slurp\Event\ExtractionEndedEvent;
use MilesAsylum\Slurp\Event\ExtractionStartedEvent;
use MilesAsylum\Slurp\Event\RecordProcessedEvent;
use MilesAsylum\Slurp\Extract\Exception\ExtractionException;
use MilesAsylum\Slurp\Extract\Exception\MalformedSourceException;
use MilesAsylum\Slurp\OuterPipeline\Exception\OuterPipelineException;
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

    /**
     * @param Slurp $slurp
     * @return Slurp
     * @throws OuterPipelineException
     */
    public function __invoke(Slurp $slurp): Slurp
    {
        $this->dispatch(ExtractionStartedEvent::NAME, new ExtractionStartedEvent());

        $extractor = $slurp->getExtractor();

        if ($extractor === null) {
            throw new OuterPipelineException(sprintf('An extractor has not been set for %s.', Slurp::class));
        }

        $iterator = new \IteratorIterator($extractor->getIterator());
        $previousRecordId = null;

        try {
            foreach ($extractor as $id => $values) {
                $payload = new SlurpPayload();
                $payload->setRecordId($id);
                $payload->setRecord($values);

                ($this->innerPipeline)($payload);

                $this->dispatch(RecordProcessedEvent::NAME, new RecordProcessedEvent());

                $interrupt = $this->interrupt;

                if ($interrupt !== null && $interrupt($slurp, $payload)) {
                    $slurp->abort();
                    $this->dispatch(
                        ExtractionAbortedEvent::NAME,
                        new ExtractionAbortedEvent(
                            'Extraction was interrupted.',
                            $id
                        )
                    );
                    break;
                }

                $previousRecordId = $id;
            }
        } catch (MalformedSourceException $e) {
            $slurp->abort();
            $this->dispatch(
                ExtractionAbortedEvent::NAME,
                new ExtractionAbortedEvent($e->getMessage(), $previousRecordId + 1)
            );
        }

        $this->dispatch(ExtractionEndedEvent::NAME, new ExtractionEndedEvent());

        return $slurp;
    }
}
