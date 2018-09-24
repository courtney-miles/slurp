<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:39 AM
 */

namespace MilesAsylum\Slurp\Stage;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use MilesAsylum\Slurp\Slurp;
use MilesAsylum\Slurp\SlurpPayload;

class InvokeExtractionPipeline implements OuterProcessStageInterface
{
    /**
     * @var Pipeline
     */
    private $innerPipeline;

    public function __construct(PipelineInterface $innerPipeline)
    {
        $this->innerPipeline = $innerPipeline;
    }

    public function __invoke(Slurp $slurp): Slurp
    {
        foreach ($slurp->getExtractor() as $id => $values) {
            $payload = new SlurpPayload();
            $payload->setRecordId($id);
            $payload->setRecord($values);

            ($this->innerPipeline)($payload);
        }

        return $slurp;
    }
}
