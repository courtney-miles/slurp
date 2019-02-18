<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:15 PM
 */

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\Event\RecordTransformedEvent;
use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Transform\TransformerInterface;

class TransformationStage extends AbstractStage
{
    /**
     * @var TransformerInterface
     */
    private $transformer;

    const STATE_BEGIN = 'begin_transformation';
    const STATE_END = 'end_transformation';

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if ($payload->hasViolations()) {
            return $payload;
        }

        $payload->setRecord(
            $this->transformer->transformRecord($payload->getRecord())
        );
        $this->dispatch(RecordTransformedEvent::NAME, new RecordTransformedEvent($payload));

        return $payload;
    }
}
