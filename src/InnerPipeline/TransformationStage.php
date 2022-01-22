<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

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
