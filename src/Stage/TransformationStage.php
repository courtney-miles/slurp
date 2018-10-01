<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:15 PM
 */

namespace MilesAsylum\Slurp\Stage;

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
        if (!$payload->hasViolations()) {
            $payload->setRecord(
                $this->transformer->transformRecord($payload->getRecord())
            );
        }

        $this->notify($payload);

        return $payload;
    }
}
