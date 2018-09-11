<?php
/**
 * Author: Courtney Miles
 * Date: 6/09/18
 * Time: 10:45 PM
 */

namespace MilesAsylum\Slurp\Stage;

abstract class AbstractStage implements StageInterface
{
    /**
     * @var StageObserverInterface[]
     */
    protected $observers = [];

    public function attachObserver(StageObserverInterface $observer)
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    protected function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
