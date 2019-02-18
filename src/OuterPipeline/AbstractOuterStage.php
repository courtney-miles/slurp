<?php
/**
 * Author: Courtney Miles
 * Date: 1/10/18
 * Time: 8:41 AM
 */

namespace MilesAsylum\Slurp\OuterPipeline;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractOuterStage implements OuterStageInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    private $state;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function getState(): string
    {
        return $this->state;
    }

    protected function dispatch(string $eventName, Event $event): void
    {
        if (isset($this->dispatcher)) {
            $this->dispatcher->dispatch($eventName, $event);
        }
    }
}
