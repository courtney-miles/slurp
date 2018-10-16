<?php
/**
 * Author: Courtney Miles
 * Date: 1/10/18
 * Time: 8:41 AM
 */

namespace MilesAsylum\Slurp\OuterPipeline;

abstract class AbstractOuterStage implements OuterStageInterface
{
    /**
     * @var OuterStageObserverInterface[]
     */
    protected $observers = [];

    /**
     * @var string
     */
    private $state;

    public function attachObserver(OuterStageObserverInterface $observer): void
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    public function getState(): string
    {
        return $this->state;
    }

    protected function notify(string $state): void
    {
        $this->state = $state;

        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
