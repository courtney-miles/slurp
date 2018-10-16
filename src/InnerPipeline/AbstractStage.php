<?php
/**
 * Author: Courtney Miles
 * Date: 6/09/18
 * Time: 10:45 PM
 */

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\SlurpPayload;

abstract class AbstractStage implements StageInterface
{
    /**
     * @var StageObserverInterface[]
     */
    protected $observers = [];

    /**
     * @var SlurpPayload
     */
    private $payload;


    public function attachObserver(StageObserverInterface $observer): void
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    protected function notify(SlurpPayload $payload): void
    {
        $this->payload = $payload;

        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function getPayload(): SlurpPayload
    {
        return $this->payload;
    }
}
