<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:07 PM
 */

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Validate\ValidatorInterface;

class ValidationStage extends AbstractStage
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if ($payload->isFiltered()) {
            return $payload;
        }

        $payload->addViolations($this->validator->validateRecord($payload->getRecordId(), $payload->getRecord()));

        $this->notify($payload);

        return $payload;
    }
}
