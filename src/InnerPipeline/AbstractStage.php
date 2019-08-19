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

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractStage implements StageInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var SlurpPayload
     */
    private $payload;

    /**
     * @var string
     */
    private $state;


    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    protected function dispatch(string $eventName, Event $event): void
    {
        if (isset($this->dispatcher)) {
            $this->dispatcher->dispatch($eventName, $event);
        }
    }

    public function getPayload(): SlurpPayload
    {
        return $this->payload;
    }

    public function getState(): ?string
    {
        return $this->state;
    }
}
