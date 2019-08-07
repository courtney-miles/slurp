<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:05 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface StageInterface
{
    public function __invoke(SlurpPayload $payload): SlurpPayload;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void;

    public function getPayload(): SlurpPayload;

    public function getState(): ?string;
}
