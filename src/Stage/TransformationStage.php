<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:15 PM
 */

namespace MilesAsylum\Slurp\Stage;

use MilesAsylum\Slurp\SlurpPayload;
use MilesAsylum\Slurp\Transform\Change;
use MilesAsylum\Slurp\Transform\Transformer;
use MilesAsylum\Slurp\Transform\TransformerBork;
use MilesAsylum\Slurp\Transform\TransformerInterface;

class TransformationStage implements StageInterface
{
    /**
     * @var int|string
     */
    private $valueName;

    /**
     * @var Change
     */
    private $change;

    /**
     * @var TransformerBork
     */
    private $transformer;

    public function __construct($valueName, Change $change, TransformerInterface $transformer)
    {
        $this->valueName = $valueName;
        $this->change = $change;
        $this->transformer = $transformer;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if ($payload->hasValue($this->valueName)) {
            if (!$payload->valueHasViolation($this->valueName)) {
                $payload->replaceValue(
                    $this->valueName,
                    $this->transformer->transform(
                        $payload->getValue($this->valueName),
                        $this->change
                    )
                );
            }
        } else {
            trigger_error(
                "A value named {$this->valueName} does not exist in the payload to validate.",
                E_USER_NOTICE
            );
        }

        return $payload;
    }
}
