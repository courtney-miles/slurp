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

    /**
     * @var SlurpPayload
     */
    protected $payload;

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if (!$payload->hasViolations()) {
            $payload->setValues(
                $this->transformer->transformRecord($payload->getValues())
            );
        }

        $this->payload = $payload;
        $this->notify();

        return $payload;
    }

    public function getPayload(): SlurpPayload
    {
        return $this->payload;
    }
}
