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
