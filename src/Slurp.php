<?php
/**
 * Author: Courtney Miles
 * Date: 12/08/18
 * Time: 6:34 PM
 */

namespace MilesAsylum\Slurp;

use League\Pipeline\Pipeline;
use MilesAsylum\Slurp\Extract\ExtractorInterface;

class Slurp
{
    /**
     * @var Pipeline
     */
    private $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function process(ExtractorInterface $extractor)
    {
        foreach ($extractor as $id => $values) {
            $payload = new SlurpPayload();
            $payload->setId($id);
            $payload->setValues($values);

            $this->pipeline->process($payload);
        }
    }
}
