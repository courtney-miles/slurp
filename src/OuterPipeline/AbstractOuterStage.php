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

namespace MilesAsylum\Slurp\OuterPipeline;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
            $this->dispatcher->dispatch($event, $eventName);
        }
    }
}
