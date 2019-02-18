<?php
/**
 * Author: Courtney Miles
 * Date: 15/10/18
 * Time: 8:46 PM
 */

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\Event\RecordFilteredEvent;
use MilesAsylum\Slurp\Filter\FilterInterface;
use MilesAsylum\Slurp\SlurpPayload;

class FiltrationStage extends AbstractStage
{
    /**
     * @var FilterInterface
     */
    private $filter;

    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        $payload->setFiltered($this->filter->filterRecord($payload->getRecord()));

        if ($payload->isFiltered()) {
            $this->dispatch(RecordFilteredEvent::NAME, new RecordFilteredEvent($payload));
        }

        return $payload;
    }
}
