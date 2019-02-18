<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:38 PM
 */

namespace MilesAsylum\Slurp\InnerPipeline;


use MilesAsylum\Slurp\Event\LoadAbortedEvent;
use MilesAsylum\Slurp\Event\RecordLoadedEvent;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\SlurpPayload;

class LoadStage extends AbstractStage
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var bool
     */
    protected $loadAborted = false;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if (!$this->loadAborted) {
            if (!$this->loader->hasBegun()) {
                $this->loader->begin();
            }

            if ($payload->hasViolations()) {
                $this->loader->abort();
                $this->loadAborted = true;
                $payload->setLoadAborted($this->loadAborted);
                $this->dispatch(LoadAbortedEvent::NAME, new LoadAbortedEvent());
            } else {
                $this->loader->loadValues($payload->getRecord());
                $this->dispatch(RecordLoadedEvent::NAME, new RecordLoadedEvent($payload));
            }
        } else {
            $payload->setLoadAborted(true);
        }

        return $payload;
    }
}
