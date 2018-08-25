<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:07 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationStage implements StageInterface
{
    private $valueName;

    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct($valueName, Constraint $constraint, ValidatorInterface $validator)
    {
        $this->valueName = $valueName;
        $this->constraint = $constraint;
        $this->validator = $validator;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if ($payload->hasValue($this->valueName)) {
            $payload->addViolations(
                $this->validator->validate(
                    $payload->getValue($this->valueName),
                    $this->constraint
                )
            );
        } else {
            trigger_error(
                "A value named {$this->valueName} does not exist in the payload to validate.",
                E_USER_NOTICE
            );
        }

        return $payload;
    }
}
