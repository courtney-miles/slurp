<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\Event\RecordValidatedEvent;
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
        $this->dispatch(RecordValidatedEvent::NAME, new RecordValidatedEvent($payload));

        return $payload;
    }
}
